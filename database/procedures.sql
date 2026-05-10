-- =============================================================================
-- Projeto: ContrataPorto
-- Arquivo: database/procedures.sql
-- Data: 2026-05-09
-- Descrição: Stored Procedures para encapsulamento das regras de negócio
--            relacionadas a candidaturas e listagem/expiração de vagas.
--
-- INSTRUÇÕES DE USO NO DBEAVER:
--   1. Conecte ao banco railway 2 (tramway.proxy.rlwy.net:19677)
--   2. Clique com botão direito em Procedures > Criar nova Procedure
--   3. Informe o nome, abra Fonte, cole o corpo e salve
--   Ou execute cada bloco CREATE individualmente via Ctrl+Enter
-- =============================================================================


-- =============================================================================
-- SP1: sp_candidatar_vaga
-- Gerencia a candidatura de um usuário a uma vaga de forma atômica.
-- Verifica se a vaga está ativa, se não há candidatura duplicada,
-- e insere o registro em uma única transação com ROLLBACK automático.
--
-- Parâmetros:
--   IN  p_user_id   INT          → ID do usuário candidato
--   IN  p_vaga_id   INT          → ID da vaga
--   IN  p_mensagem  TEXT         → Mensagem de apresentação (pode ser NULL)
--   IN  p_curriculo VARCHAR(255) → Caminho do currículo (pode ser NULL)
--   OUT p_resultado INT          → 0=sucesso | 1=vaga inativa | 2=duplicada | 99=erro
--
-- Exemplo de uso:
--   CALL sp_candidatar_vaga(1, 5, 'Tenho interesse!', '/uploads/cv.pdf', @res);
--   SELECT @res;
-- =============================================================================

CREATE PROCEDURE railway.sp_candidatar_vaga(
    IN p_user_id INT,
    IN p_vaga_id INT,
    IN p_mensagem TEXT,
    IN p_curriculo VARCHAR(255),
    OUT p_resultado INT
)
BEGIN
    DECLARE v_vaga_ativa INT;
    DECLARE v_ja_candidatou INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = 99;
    END;

    START TRANSACTION;

    -- 1. Verificar se a vaga existe e está ATIVA
    SELECT COUNT(*) INTO v_vaga_ativa
    FROM vagas
    WHERE id = p_vaga_id AND status = 'ATIVA';

    IF v_vaga_ativa = 0 THEN
        SET p_resultado = 1;
        ROLLBACK;
    ELSE
        -- 2. Verificar se o usuário já se candidatou a esta vaga
        SELECT COUNT(*) INTO v_ja_candidatou
        FROM applications
        WHERE user_id = p_user_id AND vaga_id = p_vaga_id;

        IF v_ja_candidatou > 0 THEN
            SET p_resultado = 2;
            ROLLBACK;
        ELSE
            -- 3. Inserir a candidatura
            INSERT INTO applications (
                vaga_id, user_id, mensagem, curriculo_path,
                status, created_at, updated_at
            )
            VALUES (
                p_vaga_id, p_user_id, p_mensagem, p_curriculo,
                'PENDENTE', NOW(), NOW()
            );

            SET p_resultado = 0;
            COMMIT;
        END IF;
    END IF;
END;


-- =============================================================================
-- SP2: sp_atualizar_status_candidatura
-- Atualiza o status de uma candidatura com validação de autorização.
-- Garante que a empresa só altera candidaturas de suas próprias vagas.
--
-- Parâmetros:
--   IN  p_application_id INT         → ID da candidatura
--   IN  p_empresa_id     INT         → ID da empresa (autenticada)
--   IN  p_novo_status    VARCHAR(20) → Novo status desejado
--   OUT p_resultado      INT         → 0=sucesso | 1=não autorizado | 2=status inválido
--
-- Exemplo de uso:
--   CALL sp_atualizar_status_candidatura(10, 3, 'APROVADO', @res);
--   SELECT @res;
-- =============================================================================

CREATE PROCEDURE railway.sp_atualizar_status_candidatura(
    IN p_application_id INT,
    IN p_empresa_id INT,
    IN p_novo_status VARCHAR(20),
    OUT p_resultado INT
)
BEGIN
    DECLARE v_pertence_empresa INT;

    -- 1. Validar se o status informado é permitido
    IF p_novo_status NOT IN ('PENDENTE', 'EM_ANALISE', 'APROVADO', 'RECUSADO') THEN
        SET p_resultado = 2;
    ELSE
        -- 2. Verificar se a candidatura pertence a uma vaga da empresa
        SELECT COUNT(*) INTO v_pertence_empresa
        FROM applications a
        INNER JOIN vagas v ON a.vaga_id = v.id
        WHERE a.id = p_application_id
          AND v.empresa_id = p_empresa_id;

        IF v_pertence_empresa = 0 THEN
            SET p_resultado = 1;
        ELSE
            -- 3. Atualizar o status
            UPDATE applications
            SET status = p_novo_status,
                updated_at = NOW()
            WHERE id = p_application_id;

            SET p_resultado = 0;
        END IF;
    END IF;
END;


-- =============================================================================
-- SP3: sp_listar_vagas_ativas
-- Retorna vagas ativas com JOIN de empresa e contagem de candidatos.
-- Suporta paginação e filtro opcional por título (busca textual).
--
-- Parâmetros:
--   IN p_limite INT          → Quantidade de registros por página
--   IN p_offset INT          → Deslocamento para paginação
--   IN p_busca VARCHAR(255)  → Termo de busca por título (NULL = sem filtro)
--
-- Exemplo de uso:
--   CALL sp_listar_vagas_ativas(10, 0, NULL);          -- todas as vagas
--   CALL sp_listar_vagas_ativas(10, 0, 'Desenvolvedor'); -- com filtro
-- =============================================================================

CREATE PROCEDURE railway.sp_listar_vagas_ativas(
    IN p_limite INT,
    IN p_offset INT,
    IN p_busca VARCHAR(255)
)
BEGIN
    IF p_busca IS NOT NULL AND TRIM(p_busca) != '' THEN
        -- Com filtro de busca por título
        SELECT
            v.id, v.titulo, v.descricao, v.nivel, v.area,
            v.salario_min, v.salario_max, v.tipo_contrato,
            v.tipo_vaga, v.experiencia, v.status, v.created_at,
            e.nome_fantasia, e.logo_path,
            COUNT(a.id) AS total_candidatos
        FROM vagas v
        INNER JOIN empresas e ON v.empresa_id = e.id
        LEFT JOIN applications a ON v.id = a.vaga_id
        WHERE v.status = 'ATIVA'
          AND (v.data_expiracao IS NULL OR v.data_expiracao >= CURDATE())
          AND v.titulo LIKE CONCAT('%', p_busca, '%')
        GROUP BY v.id, v.titulo, v.descricao, v.nivel, v.area,
                 v.salario_min, v.salario_max, v.tipo_contrato,
                 v.tipo_vaga, v.experiencia, v.status, v.created_at,
                 e.nome_fantasia, e.logo_path
        ORDER BY v.created_at DESC
        LIMIT p_limite OFFSET p_offset;
    ELSE
        -- Sem filtro — todas as vagas ativas
        SELECT
            v.id, v.titulo, v.descricao, v.nivel, v.area,
            v.salario_min, v.salario_max, v.tipo_contrato,
            v.tipo_vaga, v.experiencia, v.status, v.created_at,
            e.nome_fantasia, e.logo_path,
            COUNT(a.id) AS total_candidatos
        FROM vagas v
        INNER JOIN empresas e ON v.empresa_id = e.id
        LEFT JOIN applications a ON v.id = a.vaga_id
        WHERE v.status = 'ATIVA'
          AND (v.data_expiracao IS NULL OR v.data_expiracao >= CURDATE())
        GROUP BY v.id, v.titulo, v.descricao, v.nivel, v.area,
                 v.salario_min, v.salario_max, v.tipo_contrato,
                 v.tipo_vaga, v.experiencia, v.status, v.created_at,
                 e.nome_fantasia, e.logo_path
        ORDER BY v.created_at DESC
        LIMIT p_limite OFFSET p_offset;
    END IF;
END;


-- =============================================================================
-- SP4: sp_expirar_vagas_concluidas
-- Rotina de limpeza que marca como EXPIRADA toda vaga com status CONCLUIDA
-- há mais de 3 dias. Ideal para ser chamada via cron job diário.
--
-- Parâmetros:
--   OUT p_linhas_afetadas INT → Quantidade de vagas que foram expiradas
--
-- Exemplo de uso:
--   CALL sp_expirar_vagas_concluidas(@afetadas);
--   SELECT @afetadas AS vagas_expiradas;
-- =============================================================================

CREATE PROCEDURE railway.sp_expirar_vagas_concluidas(
    OUT p_linhas_afetadas INT
)
BEGIN
    -- Atualizar vagas CONCLUIDAS há mais de 3 dias para EXPIRADA
    UPDATE vagas
    SET status = 'EXPIRADA',
        updated_at = NOW()
    WHERE status = 'CONCLUIDA'
      AND updated_at < (NOW() - INTERVAL 3 DAY);

    -- Capturar quantas linhas foram afetadas
    SET p_linhas_afetadas = ROW_COUNT();
END;
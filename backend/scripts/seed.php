<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/../config/app.php';

// Segurança: Verificar token
$cronSecret = getEnv2('CRON_SECRET', 'contrata_porto_secret_123');
$token = $_GET['token'] ?? '';

if ($token !== $cronSecret) {
    http_response_code(403);
    die('Acesso negado: Token inválido.');
}

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Iniciando Seeding do Banco de Dados...</h1>";

try {
    $db = Database::getConnection();

    // 1. Limpeza
    echo "<li>Limpando tabelas existentes... ";
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    $db->exec('TRUNCATE TABLE email_verifications');
    $db->exec('TRUNCATE TABLE password_resets');
    $db->exec('TRUNCATE TABLE favorites');
    $db->exec('TRUNCATE TABLE applications');
    $db->exec('TRUNCATE TABLE vagas');
    $db->exec('TRUNCATE TABLE empresas');
    $db->exec('TRUNCATE TABLE users');
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "<span style='color:green'>OK</span></li>";

    // 2. Usuários
    $senhaPadrao = '$2y$10$E8gv9FFOllIplybesNJbpe.OCmTVLG1mu99caq750zOT7/Rb3quuW'; // Admin@1234
    
    $usuarios = [
        ['nome' => 'Tech Solutions', 'email' => 'empresa1@admin.com', 'role' => 'EMPRESA'],
        ['nome' => 'Porto Empregos', 'email' => 'empresa2@admin.com', 'role' => 'EMPRESA'],
        ['nome' => 'Indústria Porto', 'email' => 'empresa3@admin.com', 'role' => 'EMPRESA'],
        ['nome' => 'Comércio & Cia', 'email' => 'empresa4@admin.com', 'role' => 'EMPRESA'],
        ['nome' => 'Ana Silva', 'email' => 'candidato1@admin.com', 'role' => 'CANDIDATO'],
        ['nome' => 'Bruno Costa', 'email' => 'candidato2@admin.com', 'role' => 'CANDIDATO'],
        ['nome' => 'Carla Mendes', 'email' => 'candidato3@admin.com', 'role' => 'CANDIDATO'],
        ['nome' => 'Diego Rocha', 'email' => 'candidato4@admin.com', 'role' => 'CANDIDATO'],
    ];

    echo "<li>Inserindo usuários... ";
    $stmtUser = $db->prepare("INSERT INTO users (nome, email, senha, role, email_verified, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
    foreach ($usuarios as $u) {
        $stmtUser->execute([$u['nome'], $u['email'], $senhaPadrao, $u['role']]);
    }
    echo "<span style='color:green'>" . count($usuarios) . " inseridos</span></li>";

    // 3. Empresas
    $empresas = [
        ['email' => 'empresa1@admin.com', 'fantasia' => 'Tech Solutions Porto Ferreira', 'razao' => 'Tech Solutions LTDA', 'cnpj' => '11.111.111/0001-11'],
        ['email' => 'empresa2@admin.com', 'fantasia' => 'Porto Empregos', 'razao' => 'Porto Empregos LTDA', 'cnpj' => '22.222.222/0002-22'],
        ['email' => 'empresa3@admin.com', 'fantasia' => 'Indústria Porto Ferreira', 'razao' => 'Indústria Porto LTDA', 'cnpj' => '33.333.333/0003-33'],
        ['email' => 'empresa4@admin.com', 'fantasia' => 'Comércio & Cia Porto Ferreira', 'razao' => 'Comércio e Cia LTDA', 'cnpj' => '44.444.444/0004-44'],
    ];

    echo "<li>Vinculando empresas... ";
    $stmtEmpresa = $db->prepare("INSERT INTO empresas (user_id, nome_fantasia, razao_social, cnpj, email_contato, cidade, estado, created_at) VALUES (?, ?, ?, ?, ?, 'Porto Ferreira', 'SP', NOW())");
    
    $empresaIds = [];
    foreach ($empresas as $e) {
        $userId = $db->query("SELECT id FROM users WHERE email = '{$e['email']}'")->fetchColumn();
        $stmtEmpresa->execute([$userId, $e['fantasia'], $e['razao'], $e['cnpj'], $e['email']]);
        $empId = $db->lastInsertId();
        $empresaIds[$e['email']] = $empId;
        
        // Atualiza empresa_id no usuário
        $db->exec("UPDATE users SET empresa_id = $empId WHERE id = $userId");
    }
    echo "<span style='color:green'>" . count($empresas) . " empresas criadas</span></li>";

    // 4. Vagas
    echo "<li>Inserindo 40 vagas... ";
    $stmtVaga = $db->prepare("INSERT INTO vagas (empresa_id, titulo, cargo, area, descricao, requisitos, beneficios, tipo_contrato, tipo_vaga, nivel, experiencia, carga_horaria, salario_min, salario_max) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $vagasData = [
        // Empresa 1
        'empresa1@admin.com' => [
            ['Analista de Suporte Júnior', 'Analista de Suporte', 'TI', "Estamos em busca de um Analista de Suporte para auxiliar nossos clientes internos e externos.\nResponsável por manutenção de hardware, software e suporte em redes locais.", "- Ensino Superior em TI (cursando ou completo)\n- Conhecimento básico em redes\n- Pacote Office avançado", "- Plano de Saúde\n- Vale Refeição\n- Seguro de Vida", 'CLT', 'PRESENCIAL', 'Júnior', '6_MESES_A_1_ANO', '44h semanais', 2200, 2800],
            ['Desenvolvedor Full Stack PHP', 'Desenvolvedor', 'TI', "Buscamos desenvolvedor proativo para atuar em projetos de e-commerce e sistemas internos.\nFoco em PHP moderno, MySQL e frameworks JavaScript.", "- PHP 8+\n- MySQL / PostgreSQL\n- HTML/CSS/JS\n- Git", "- Home Office (Híbrido)\n- Bônus por Metas\n- Cursos", 'CLT', 'HIBRIDO', 'Pleno', '1_A_3_ANOS', '40h semanais', 4500, 6500],
            ['Auxiliar Administrativo', 'Auxiliar Administrativo', 'Administração', "Atendimento telefônico, organização de documentos e auxílio no faturamento.\nUso constante de planilhas de controle.", "- Ensino Médio completo\n- Conhecimento em Excel\n- Boa escrita", "- Vale Transporte\n- Cesta Básica\n- Convênio Farmácia", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1600, 1900],
            ['Técnico de Infraestrutura', 'Técnico de TI', 'TI', "Manutenção de servidores e cabeamento estruturado.\nInstalação de câmeras e sistemas de segurança digital.", "- Formação Técnica em TI\n- Experiência com Mikrotik\n- CNH B", "- Veículo da empresa\n- Diárias de Viagem", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2800, 3500],
            ['Secretária Executiva', 'Secretária', 'Administração', "Gestão de agenda da diretoria, recepção de clientes e organização de eventos.", "- Inglês intermediário\n- Experiência anterior na função\n- Discrição", "- Plano Odontológico\n- PLR Semestral\n- Refeitório", 'CLT', 'PRESENCIAL', 'Sênior', 'MAIS_DE_3_ANOS', '44h semanais', 3000, 4200],
            ['Analista Financeiro', 'Analista Financeiro', 'Administração', "Conciliação bancária, contas a pagar e receber e emissão de notas fiscais.", "- Formação em Administração ou Contábeis\n- Excel Avançado", "- Plano de Saúde\n- Vale Alimentação\n- Auxílio Educação", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 3500, 4800],
            ['Web Designer', 'Designer', 'Design', "Criação de interfaces para sites e landing pages.\nDesenvolvimento de banners para redes sociais.", "- Domínio de Figma ou Adobe XD\n- Photoshop e Illustrator", "- Bônus por performance\n- Horário flexível\n- Day off", 'CLT', 'REMOTO', 'Pleno', '1_A_3_ANOS', '40h semanais', 3200, 4500],
            ['Estagiário de Programação', 'Estagiário', 'TI', "Auxílio na correção de bugs e implementação de pequenas features.\nAprendizado prático em ambiente profissional.", "- Cursando TI (noturno)\n- Lógica de programação sólida", "- Bolsa Auxílio\n- Seguro de Vida\n- Efetivação", 'Estágio', 'HIBRIDO', 'Sem experiência', 'SEM_EXPERIENCIA', '30h semanais', 1000, 1200],
            ['Gerente de TI', 'Gerente', 'TI', "Gestão de equipe de desenvolvimento e suporte.\nPlanejamento estratégico de infraestrutura.", "- Pós-graduação na área\n- Experiência em liderança\n- Inglês Avançado", "- Carro da empresa\n- Notebook e Celular\n- Bônus", 'CLT', 'PRESENCIAL', 'Gestão', 'MAIS_DE_3_ANOS', '44h semanais', 8000, 12000],
            ['Analista de Dados (BI)', 'Analista de BI', 'TI', "Criação de dashboards e relatórios para tomada de decisão estratégica.", "- SQL Avançado\n- Power BI ou Tableau\n- Estatística", "- Participação nos Lucros\n- Auxílio Inglês\n- Gympass", 'PJ', 'REMOTO', 'Sênior', 'MAIS_DE_3_ANOS', '40h semanais', 6000, 9000],
        ],
        // Empresa 2
        'empresa2@admin.com' => [
            ['Vendedor de Loja', 'Vendedor', 'Comércio', "Atendimento ao público em loja de decoração e presentes.\nOrganização de vitrines.", "- Ensino Médio completo\n- Disponibilidade de horário", "- Comissão\n- VT\n- Treinamentos", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1500, 2500],
            ['Motorista Entregador (Cat C/D)', 'Motorista', 'Logística', "Entregas de mercadorias em Porto Ferreira e região.\nConferência de notas fiscais.", "- CNH C ou D válida\n- Experiência com caminhão", "- Vale Refeição\n- Diárias\n- Seguro", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2500, 3200],
            ['Operador de Caixa', 'Caixa', 'Comércio', "Operação de caixa em supermercado de grande porte.\nRecebimento de valores.", "- Ensino Médio completo\n- Agilidade numérica", "- Quebra de Caixa\n- VT\n- Auxílio Creche", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1600, 1850],
            ['Estoquista / Conferente', 'Estoquista', 'Logística', "Recebimento de mercadorias, conferência com nota fiscal.", "- Ensino Médio\n- Proatividade", "- Cesta Básica\n- Vale Refeição\n- Convênio Médico", 'CLT', 'PRESENCIAL', 'Júnior', '6_MESES_A_1_ANO', '44h semanais', 1700, 2000],
            ['Auxiliar de Limpeza', 'Auxiliar de Limpeza', 'Serviços Gerais', "Limpeza e conservação de ambientes de escritório e áreas comuns.", "- Alfabetizado\n- Experiência na função", "- VT\n- Uniforme\n- Cesta Básica", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1450, 1600],
            ['Recepcionista de Consultório', 'Recepcionista', 'Saúde', "Agendamento de consultas, atendimento telefônico e recepção de pacientes.", "- Ensino Médio completo\n- Informática básica", "- VT\n- Convênio Médico", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1650, 1900],
            ['Fisioterapeuta', 'Fisioterapeuta', 'Saúde', "Atendimento clínico em clínica de reabilitação física.\nRealização de avaliações.", "- Graduação em Fisioterapia\n- CREFITO ativo", "- Honorários\n- Ambiente climatizado", 'PJ', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '30h semanais', 3000, 5000],
            ['Cozinheiro(a)', 'Cozinheiro', 'Comércio', "Preparação de pratos à la carte e self-service.", "- Experiência comprovada\n- Manipulação de Alimentos", "- Refeição no local\n- Gorjetas\n- VT", 'CLT', 'PRESENCIAL', 'Sênior', 'MAIS_DE_3_ANOS', '44h semanais', 2200, 3500],
            ['Analista de RH (Recrutamento)', 'Analista de RH', 'RH', "Triagem de currículos, realização de entrevistas e integração.", "- Formação em Psicologia ou RH\n- Experiência em R&S", "- Auxílio Educação\n- Plano de Saúde\n- Vale Alimentação", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2800, 3800],
            ['Gerente de Loja', 'Gerente', 'Comércio', "Gestão completa de unidade de varejo, equipe e metas.", "- Experiência em gestão\n- Foco em resultados", "- PLR\n- Veículo\n- Odonto", 'CLT', 'PRESENCIAL', 'Gestão', 'MAIS_DE_3_ANOS', '44h semanais', 4500, 7000],
        ],
        // Empresa 3
        'empresa3@admin.com' => [
            ['Operador de Forno Cerâmico', 'Operador de Forno', 'Produção', "Operação de fornos contínuos em indústria de cerâmica artística.", "- Experiência em cerâmica\n- Disponibilidade para turnos", "- Adicional Noturno\n- Cesta Básica\n- Saúde", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2000, 2600],
            ['Auxiliar de Produção (Geral)', 'Auxiliar de Produção', 'Produção', "Atuar em moldagem, acabamento e embalagem.", "- Disposição física\n- Ensino Fundamental", "- Refeitório\n- VT\n- Assiduidade", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1550, 1800],
            ['Pintor Artístico (Cerâmica)', 'Pintor', 'Produção', "Pintura manual de peças de cerâmica conforme modelos.", "- Habilidade manual\n- Experiência com pintura", "- Bônus produtividade\n- Farmácia", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 1800, 2500],
            ['Eletricista de Manutenção', 'Eletricista', 'Produção', "Manutenção corretiva e preventiva de máquinas industriais.", "- Técnico em Elétrica\n- NR10 válida", "- Periculosidade (30%)\n- Saúde\n- Alimentação", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2800, 4000],
            ['Mecânico Industrial', 'Mecânico', 'Produção', "Manutenção de prensas, compressores e sistemas pneumáticos.", "- Curso de Mecânica (SENAI)\n- Experiência em fábrica", "- Vale Refeição\n- Seguro\n- Auxílio Ferramentas", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2700, 3800],
            ['Líder de Expedição', 'Líder', 'Logística', "Coordenação da equipe de carregamento e conferência.", "- Experiência em logística industrial\n- Liderança", "- Adicional Cargo de Confiança\n- PLR Anual\n- Odonto", 'CLT', 'PRESENCIAL', 'Sênior', 'MAIS_DE_3_ANOS', '44h semanais', 3200, 4500],
            ['Analista de Qualidade', 'Analista de Qualidade', 'Produção', "Inspeção de matéria-prima e produtos acabados.", "- Engenharia ou Produção\n- Ferramentas da qualidade", "- Vale Alimentação\n- Saúde\n- Auxílio Creche", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 3000, 4200],
            ['Torneiro Mecânico', 'Torneiro', 'Produção', "Uso de torno convencional para peças de reposição.", "- Curso de Torneiro\n- Experiência comprovada", "- Insalubridade\n- Cesta Básica\n- Café da manhã", 'CLT', 'PRESENCIAL', 'Sênior', 'MAIS_DE_3_ANOS', '44h semanais', 2800, 3900],
            ['Assistente de PCP', 'Assistente', 'Produção', "Auxílio no Planejamento e Controle da Produção.", "- Estudante de Engenharia\n- Excel intermediário", "- Bolsa Auxílio\n- Crescimento", 'CLT', 'PRESENCIAL', 'Júnior', '6_MESES_A_1_ANO', '44h semanais', 1800, 2300],
            ['Encarregado de Produção', 'Encarregado', 'Produção', "Gestão direta da linha de produção e metas.", "- Experiência em chão de fábrica\n- Gestão de pessoas", "- PLR\n- Veículo\n- Plano Saúde Top", 'CLT', 'PRESENCIAL', 'Gestão', 'MAIS_DE_3_ANOS', '44h semanais', 4500, 6500],
        ],
        // Empresa 4
        'empresa4@admin.com' => [
            ['Professor(a) de Inglês', 'Professor', 'Educação', "Ministrar aulas para crianças, jovens e adultos.", "- Fluência no idioma\n- Gosto pelo ensino", "- VT\n- Desconto em cursos\n- Treinamentos", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '20h a 40h semanais', 1800, 3500],
            ['Monitor(a) Escolar', 'Monitor', 'Educação', "Auxílio aos professores e supervisão de alunos.", "- Ensino Médio completo\n- Paciência", "- VT\n- Cesta Básica\n- Farmácia", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1500, 1700],
            ['Vendedor de Veículos', 'Vendedor', 'Comércio', "Atendimento a clientes em concessionária.", "- Vendas consultivas\n- CNH B válida", "- Comissões\n- Treinamentos\n- Saúde", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 2000, 8000],
            ['Repositor de Mercadorias', 'Repositor', 'Comércio', "Reposição de produtos em prateleiras.", "- Agilidade\n- Disponibilidade horário", "- VT\n- Refeição subsidiada\n- Odonto", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1550, 1750],
            ['Telemarketing Ativo', 'Operador de Telemarketing', 'Comércio', "Realização de chamadas para oferta de serviços.", "- Boa dicção\n- Ensino Médio completo", "- Salário Fixo + Variável\n- VT e VR\n- Convênio faculdades", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '36h semanais', 1412, 2500],
            ['Auxiliar de Cozinha', 'Auxiliar de Cozinha', 'Serviços Gerais', "Auxílio no preparo, higienização e limpeza.", "- Agilidade\n- Experiência básica", "- Refeição no local\n- Gorjetas\n- VT", 'CLT', 'PRESENCIAL', 'Júnior', 'SEM_EXPERIENCIA', '44h semanais', 1500, 1750],
            ['Enfermeiro(a) do Trabalho', 'Enfermeiro', 'Saúde', "Atendimento de primeiros socorros em indústria.", "- Graduação em Enfermagem + Especialização", "- Saúde Superior\n- Seguro\n- Vale Alimentação", 'CLT', 'PRESENCIAL', 'Pleno', '1_A_3_ANOS', '44h semanais', 3800, 5500],
            ['Analista de Marketing Social', 'Analista de Marketing', 'Comércio', "Gestão de redes sociais e conteúdo.", "- Ensino Superior Marketing\n- Domínio Redes Sociais", "- Notebook\n- Home Office flexível", 'PJ', 'HIBRIDO', 'Pleno', '1_A_3_ANOS', '40h semanais', 2500, 4000],
            ['Padeiro / Confeiteiro', 'Padeiro', 'Comércio', "Produção de pães, bolos e doces.", "- Experiência comprovada\n- Habilidade com massas", "- Cesta Básica\n- Adicional Horário\n- VT", 'CLT', 'PRESENCIAL', 'Sênior', 'MAIS_DE_3_ANOS', '44h semanais', 2200, 3800],
            ['Coordenador Pedagógico', 'Coordenador', 'Educação', "Gestão da equipe de professores e suporte.", "- Graduação em Pedagogia\n- Experiência em coordenação", "- PLR\n- Saúde\n- Auxílio Alimentação", 'CLT', 'PRESENCIAL', 'Gestão', 'MAIS_DE_3_ANOS', '44h semanais', 4000, 6000],
        ],
    ];

    $totalVagas = 0;
    foreach ($vagasData as $email => $vagas) {
        $empId = $empresaIds[$email];
        foreach ($vagas as $v) {
            $params = array_merge([$empId], $v);
            $stmtVaga->execute($params);
            $totalVagas++;
        }
    }
    echo "<span style='color:green'>$totalVagas vagas criadas</span></li>";

    echo "<h3>Seed concluído! " . count($usuarios) . " usuários, " . count($empresas) . " empresas, $totalVagas vagas criadas.</h3>";
    echo "<p><a href='../../index.html' style='color:blue'>Voltar para o site</a></p>";

} catch (Exception $e) {
    echo "<h3><span style='color:red'>Erro no Seeding:</span></h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

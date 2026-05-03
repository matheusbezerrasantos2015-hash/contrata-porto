-- SEED DATA PARA CONTRATAPORTO
-- Unificado e Atualizado: 2026-04-04
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- LIMPAR TABELAS
TRUNCATE TABLE favorites;
TRUNCATE TABLE applications;
TRUNCATE TABLE vagas;
TRUNCATE TABLE empresas;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USUÁRIOS DE TESTE (Senha: 123456 - bcrypt)
INSERT INTO users (id, nome, email, senha, role, telefone, cidade, created_at) VALUES 
(1, 'João Candidato', 'teste@gmail.com', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'CANDIDATO', '(19) 99999-1111', 'Porto Ferreira', NOW()),
(2, 'Empresa Teste SA', 'empresa@gmail.com', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', '(19) 3581-2222', 'Porto Ferreira', NOW()),
(3, 'Cerâmica Porto', 'contato@ceramicaporto.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', '(19) 3581-3333', 'Porto Ferreira', NOW()),
(4, 'Vidros & Cia', 'rh@vidrosecia.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', '(19) 3581-4444', 'Porto Ferreira', NOW()),
(5, 'Logística Ferreira', 'vagas@logferreira.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', '(19) 3581-5555', 'Porto Ferreira', NOW());

-- 2. EMPRESAS (Fictícias e Reais)
INSERT INTO empresas (id, user_id, nome_fantasia, razao_social, cnpj, email_contato, telefone, cidade, estado, setor) VALUES 
(1, 2, 'Empresa Teste', 'Empresa Teste SA', '11.111.111/0001-11', 'empresa@gmail.com', '(19) 3581-2222', 'Porto Ferreira', 'SP', 'Tecnologia'),
(2, 3, 'Cerâmica Porto Ferreira', 'Cerâmica Porto Ferreira LTDA', '22.222.222/0001-22', 'contato@ceramicaporto.com.br', '(19) 3581-3333', 'Porto Ferreira', 'SP', 'Indústria Cerâmica'),
(3, 4, 'Vidros & Cia', 'Vidros e Companhia SA', '33.333.333/0001-33', 'rh@vidrosecia.com.br', '(19) 3581-4444', 'Porto Ferreira', 'SP', 'Comércio Varejista'),
(4, 5, 'Logística Ferreira', 'Logística Porto Ferreira EIRELI', '44.444.444/0001-44', 'vagas@logferreira.com.br', '(19) 3581-5555', 'Porto Ferreira', 'SP', 'Transporte e Logística');

-- 3. 40 VAGAS REALISTAS E VARIADAS
INSERT INTO vagas (empresa_id, titulo, cargo, descricao, requisitos, diferenciais, beneficios, salario_min, salario_max, tipo_contrato, tipo_vaga, experiencia, carga_horaria, area, nivel, cidade, estado, status, publicada_em, data_expiracao) VALUES 
(2, 'Operador de Forno', 'Operador de Forno', 'Buscamos um profissional dedicado para atuar como Operador de Forno em nossa sede em Porto Ferreira...', '- Experiência comprovada', '- Cursos', '- VT, VR', 1800, 3300, 'PJ', 'HIBRIDO', 'SEM_EXPERIENCIA', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Vendedor Interno', 'Vendedor Interno', 'Vendas de vidros...', '- Experiência em vendas', '- Cursos de atendimento', '- CLT usual', 2200, 3700, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Motorista Categoria D', 'Motorista Categoria D', 'Transporte de cargas...', '- CNH D', '- Experiência em rodovias', '- Diárias', 3500, 5000, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Analista de Logística', 'Analista de Logística', 'Controle de frotas...', '- Superior em Logística', '- ERP', '- Plano de Saúde', 4000, 5500, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Designer Gráfico (Cerâmica)', 'Designer Gráfico (Cerâmica)', 'Criação de artes...', '- Photoshop/Corel', '- Experiência em cerâmica', '- PLR', 3800, 5300, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Auxiliar Administrativo', 'Auxiliar Administrativo', 'Rotinas de escritório...', '- Informática básica', '- Atendimento', '- VR', 1600, 3100, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Administração', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Desenvolvedor PHP Laravel', 'Desenvolvedor PHP Laravel', 'Evolução do portal...', '- PHP 8, Laravel 10', '- MySQL', '- Home Office parcial', 8000, 9500, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Tecnologia', 'Sênior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Gerente Comercial', 'Gerente Comercial', 'Gestão de equipe...', '- MBA', '- Liderança', '- Carro da empresa', 6000, 7500, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44h', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Auxiliar de Produção', 'Auxiliar de Produção', 'Ajudar na linha...', '- Fundamental', '- Aprender', '- Cesta básica', 1500, 3000, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Estagiário de Marketing', 'Estagiário de Marketing', 'Redes sociais...', '- Cursando Mkt', '- Criativo', '- Bolsa auxílio', 1100, 2600, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Marketing', 'Estágio', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Operador de Forno - Vaga 11', 'Operador de Forno', 'Buscamos um profissional dedicado...', '- Exp comprovada', '- Compromentimento', '- VT, VR', 1800, 3300, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Vendedor Interno - Vaga 12', 'Vendedor Interno', 'Vendas internas...', '- Vendas exp', '- Atendimento', '- CLT', 2200, 3700, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Motorista Categoria D - Vaga 13', 'Motorista Categoria D', 'Entregas pesadas...', '- CNH D', '- Estradas', '- Diárias', 3500, 5000, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Analista de Logística - Vaga 14', 'Analista de Logística', 'Frotas...', '- Logística exp', '- ERP', '- Plano Saúde', 4000, 5500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Designer Gráfico (Cerâmica) - Vaga 15', 'Designer Gráfico (Cerâmica)', 'Artes cerâmicas...', '- Design softwares', '- Setor industrial', '- Plano Saúde', 3800, 5300, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44h', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Auxiliar Administrativo - Vaga 16', 'Auxiliar Administrativo', 'Apoio administrativo...', '- Informática', '- Organização', '- VR', 1600, 3100, 'PJ', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Administração', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Desenvolvedor PHP Laravel - Vaga 17', 'Desenvolvedor PHP Laravel', 'Full-stack PHP...', '- Laravel/PHP', '- MySQL', '- Remoto', 8000, 9500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Tecnologia', 'Sênior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Gerente Comercial - Vaga 18', 'Gerente Comercial', 'Liderança comercial...', '- Gestão', '- Vendas', '- Bonificações', 6000, 7500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Auxiliar de Produção - Vaga 19', 'Auxiliar de Produção', 'Produção geral...', '- Resistência', '- Vontade', '- Seguro vida', 1500, 3000, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Estagiário de Marketing - Vaga 20', 'Estagiário de Marketing', 'Criação conteúdo...', '- Graduação', '- Redes sociais', '- Aprendizado', 1100, 2600, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Marketing', 'Estágio', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Operador de Forno - Vaga 21', 'Operador de Forno', 'Forno cerâmico...', '- Exp setor', '- Turnos', '- VR', 1800, 3300, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Vendedor Interno - Vaga 22', 'Vendedor Interno', 'Balcão e fone...', '- Comunicativo', '- Vendas', '- Comissões', 2200, 3700, 'CLT', 'HIBRIDO', 'SEM_EXPERIENCIA', '44h', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Motorista Categoria D - Vaga 23', 'Motorista Categoria D', 'Caminhão médio...', '- CNH D', '- Disciplina', '- Diárias', 3500, 5000, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Analista de Logística - Vaga 24', 'Analista de Logística', 'Excel e ERP...', '- Analítico', '- Organizado', '- Plano Saúde', 4000, 5500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Designer Gráfico (Cerâmica) - Vaga 25', 'Designer Gráfico (Cerâmica)', 'Catálogos...', '- Criativo', '- Agilidade', '- VR', 3800, 5300, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Auxiliar Administrativo - Vaga 26', 'Auxiliar Administrativo', 'Arquivo e notas...', '- Atenção', '- Informática', '- Plano Saúde', 1600, 3100, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Administração', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Desenvolvedor PHP Laravel - Vaga 27', 'Desenvolvedor PHP Laravel', 'Backend Dev...', '- Laravel 10', '- Git', '- Estabilidade', 8000, 9500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Tecnologia', 'Sênior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Gerente Comercial - Vaga 28', 'Gerente Comercial', 'Estratégia comercial...', '- Visão negócio', '- Resultados', '- PLR', 6000, 7500, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Auxiliar de Produção - Vaga 29', 'Auxiliar de Produção', 'Carga e descarga...', '- Disposição', '- Equipe', '- VT', 1500, 3000, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Estagiário de Marketing - Vaga 30', 'Estagiário de Marketing', 'Social Media...', '- Design básico', '- Comunicativo', '- Bolsa', 1100, 2600, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Marketing', 'Estágio', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Operador de Forno - Vaga 31', 'Operador de Forno', 'Controle térmico...', '- Técnico', '- Observação', '- Plano Saúde', 1800, 3300, 'PJ', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Vendedor Interno - Vaga 32', 'Vendedor Interno', 'CRM e Telefone...', '- Persistência', '- Metas', '- Autonomia', 2200, 3700, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Motorista Categoria D - Vaga 33', 'Motorista Categoria D', 'Logística regional...', '- CNH D ativa', '- Rotas SP', '- Diárias', 3500, 5000, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(4, 'Analista de Logística - Vaga 34', 'Analista de Logística', 'Gestão estoque...', '- Inventários', '- Excel', '- Refeitório', 4000, 5500, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Designer Gráfico (Cerâmica) - Vaga 35', 'Designer Gráfico (Cerâmica)', 'Design interiores...', '- Sensibilidade', '- Softwares', '- Plano Saúde', 3800, 5300, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Auxiliar Administrativo - Vaga 36', 'Auxiliar Administrativo', 'Expediente...', '- Rotinas', '- Agilidade', '- VR', 1600, 3100, 'PJ', 'HIBRIDO', '1_A_3_ANOS', '44h', 'Administração', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Desenvolvedor PHP Laravel - Vaga 37', 'Desenvolvedor PHP Laravel', 'Manutenção sistemas...', '- PHP Moderno', '- Clean Code', '- CLT', 8000, 9500, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Tecnologia', 'Sênior', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(3, 'Gerente Comercial - Vaga 38', 'Gerente Comercial', 'Head Comercial...', '- Carreira', '- Exp área', '- Resultados', 6000, 7500, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(2, 'Auxiliar de Produção - Vaga 39', 'Auxiliar de Produção', 'Serviços braçais...', '- Força', '- Vigor', '- VT', 1500, 3000, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44h', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
(1, 'Estagiário de Marketing - Vaga 40', 'Estagiário de Marketing', 'Suporte marketing...', '- Inovação', '- Aprendizado', '- Bolsa', 1100, 2600, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44h', 'Marketing', 'Estágio', 'Porto Ferreira', 'SP', 'ATIVA', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));

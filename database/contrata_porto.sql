-- Schema completo e atualizado do ContrataPorto
-- Inclui todas as tabelas na ordem correta (respeitando FKs)

DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS vagas;
DROP TABLE IF EXISTS empresas;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    role ENUM('CANDIDATO','EMPRESA') NOT NULL DEFAULT 'CANDIDATO',
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    empresa_id INT NULL DEFAULT NULL,
    telefone VARCHAR(20) NULL DEFAULT NULL,
    deleted_at DATETIME NULL DEFAULT NULL,
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW() ON UPDATE NOW()
);

CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    razao_social VARCHAR(255) NOT NULL,
    cnpj VARCHAR(20) NOT NULL UNIQUE,
    email_contato VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL DEFAULT NULL,
    descricao TEXT NULL DEFAULT NULL,
    logo_path VARCHAR(255) NULL DEFAULT NULL,
    cidade VARCHAR(100) NOT NULL DEFAULT 'Porto Ferreira',
    estado CHAR(2) NOT NULL DEFAULT 'SP',
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW() ON UPDATE NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    requisitos TEXT NULL,
    beneficios TEXT NULL,
    diferenciais TEXT NULL,
    tipo_contrato VARCHAR(50) NOT NULL DEFAULT 'CLT',
    tipo_vaga VARCHAR(50) NOT NULL DEFAULT 'Presencial',
    nivel VARCHAR(50) NOT NULL DEFAULT 'Pleno',
    area VARCHAR(100) NULL,
    experiencia VARCHAR(100) NULL,
    carga_horaria VARCHAR(50) NULL,
    salario_min DECIMAL(10,2) NULL,
    salario_max DECIMAL(10,2) NULL,
    status ENUM('ATIVA','PAUSADA','CONCLUIDA','EXPIRADA') NOT NULL DEFAULT 'ATIVA',
    data_expiracao DATE NULL,
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW() ON UPDATE NOW(),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaga_id INT NOT NULL,
    user_id INT NOT NULL,
    mensagem TEXT NULL,
    curriculo_path VARCHAR(255) NULL,
    linkedin VARCHAR(255) NULL,
    portfolio VARCHAR(255) NULL,
    telefone VARCHAR(20) NULL,
    status ENUM('PENDENTE','EM_ANALISE','APROVADO','RECUSADO') 
           NOT NULL DEFAULT 'PENDENTE',
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW() ON UPDATE NOW(),
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidatura (vaga_id, user_id)
);

CREATE TABLE favorites (
    user_id INT NOT NULL,
    vaga_id INT NOT NULL,
    created_at DATETIME DEFAULT NOW(),
    PRIMARY KEY (user_id, vaga_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT NOW(),
    INDEX idx_email (email),
    INDEX idx_token (token)
);

CREATE TABLE email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code CHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code)
);

-- Stored Procedures
-- (ver database/procedures.sql para as 4 SPs do projeto)

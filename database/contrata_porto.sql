-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/05/2026 às 18:27
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `contrata_porto`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `curriculo_path` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `portfolio` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `status` enum('PENDENTE','EM_ANALISE','APROVADO','RECUSADO') DEFAULT 'PENDENTE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `applications`
--

INSERT INTO `applications` (`id`, `vaga_id`, `user_id`, `mensagem`, `curriculo_path`, `linkedin`, `portfolio`, `telefone`, `status`, `created_at`, `updated_at`) VALUES
(10, 46, 16, 'eu não quero', NULL, '', '', '', 'PENDENTE', '2026-04-15 03:55:36', '2026-04-15 03:55:36'),
(20, 46, 29, 'Candidatura de auditoria — sem currículo', NULL, 'https://linkedin.com/in/audit-teste', NULL, '11999990001', 'RECUSADO', '2026-04-15 17:18:24', '2026-04-23 02:20:12'),
(21, 55, 29, 'Candidatura de auditoria na vaga da empresa — para testar fluxo completo', NULL, 'https://linkedin.com/in/audit', NULL, '11888880001', 'RECUSADO', '2026-04-15 17:18:44', '2026-04-15 17:19:01'),
(23, 57, 32, 'Ola, tenho muito interesse nesta vaga de desenvolvedor.', NULL, 'linkedin.com/in/testecandidato', 'github.com/testecandidato', '(19) 98765-4321', 'PENDENTE', '2026-04-18 15:02:17', '2026-04-18 15:02:17'),
(27, 5, 35, 'quero me candidatar', NULL, '', '', '', 'PENDENTE', '2026-04-23 02:17:56', '2026-04-23 02:17:56'),
(28, 46, 35, 'querp', NULL, '', '', '', 'APROVADO', '2026-04-23 02:18:54', '2026-04-23 02:19:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nome_fantasia` varchar(255) NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `email_contato` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `setor` varchar(100) DEFAULT NULL,
  `num_funcionarios` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `empresas`
--

INSERT INTO `empresas` (`id`, `user_id`, `nome_fantasia`, `razao_social`, `cnpj`, `email_contato`, `telefone`, `site`, `cidade`, `estado`, `setor`, `num_funcionarios`, `descricao`, `logo_path`, `created_at`, `updated_at`) VALUES
(2, 3, 'Cerâmica Porto Ferreira', 'Cerâmica Porto Ferreira LTDA', '22.222.222/0001-22', 'contato@ceramicaporto.com.br', '(19) 3581-3333', NULL, 'Porto Ferreira', 'SP', 'Indústria Cerâmica', NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(3, 4, 'Vidros & Cia', 'Vidros e Companhia SA', '33.333.333/0001-33', 'rh@vidrosecia.com.br', '(19) 3581-4444', NULL, 'Porto Ferreira', 'SP', 'Comércio Varejista', NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(4, 5, 'Logística Ferreira', 'Logística Porto Ferreira EIRELI', '44.444.444/0001-44', 'vagas@logferreira.com.br', '(19) 3581-5555', NULL, 'Porto Ferreira', 'SP', 'Transporte e Logística', NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(6, NULL, 'jj', 'jj', '0000000000000', 'jj@gmail.com', NULL, NULL, 'Porto Ferreira', 'SP', NULL, NULL, NULL, NULL, '2026-04-15 03:41:02', '2026-04-15 03:41:02'),
(13, NULL, 'Empresa Audit Editada', 'Empresa Audit Razao Social LTDA', '12.345.678/0001-99', 'audit_empresa@teste.com', NULL, NULL, 'Porto Ferreira', 'SP', NULL, NULL, NULL, NULL, '2026-04-15 17:18:38', '2026-04-15 17:19:12'),
(14, NULL, 'Empresa Teste V2', 'Empresa Teste V2 LTDA', '98765432000188', 'contatov2@empresa.com', NULL, NULL, 'Porto Ferreira', 'SP', NULL, NULL, NULL, NULL, '2026-04-18 14:52:04', '2026-04-18 14:52:04'),
(15, NULL, 'Ui Test Corp', 'UI Test LTDA', '12345678000199', 'empresa_ui_test5@example.com', NULL, NULL, 'Porto Ferreira', 'SP', NULL, NULL, NULL, NULL, '2026-04-19 16:13:33', '2026-04-19 16:13:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `vaga_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `favorites`
--

INSERT INTO `favorites` (`user_id`, `vaga_id`, `created_at`) VALUES
(16, 46, '2026-04-15 03:55:05'),
(35, 5, '2026-04-23 02:18:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`, `expires_at`) VALUES
('teste@gmail.com', 'a4eed61b78d7e8c5ed9bb2215b03713a08b1521c63ca444513e607498d27f896', '2026-04-15 01:58:00', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `role` enum('CANDIDATO','EMPRESA','ADMIN') DEFAULT 'CANDIDATO',
  `empresa_id` int(11) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `portfolio` varchar(255) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `nivel_experiencia` varchar(50) DEFAULT NULL,
  `sobre` text DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `senha`, `role`, `empresa_id`, `telefone`, `cidade`, `linkedin`, `portfolio`, `area`, `nivel_experiencia`, `sobre`, `avatar_path`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Novo Nome Teste', 'teste@gmail.com', '$2y$10$PeLMbN8emTT/WLKDb4QbtesRsN7uJFeMv5dpsj8Zhlo8W9PeisTom', 'CANDIDATO', NULL, '(16) 98888-7777', 'Porto Ferreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-27 23:03:13'),
(3, 'Cerâmica Porto', 'contato@ceramicaporto.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', NULL, '(19) 3581-3333', 'Porto Ferreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(4, 'Vidros & Cia', 'rh@vidrosecia.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', NULL, '(19) 3581-4444', 'Porto Ferreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(5, 'Logística Ferreira', 'vagas@logferreira.com.br', '$2y$10$V/LEbCpXto1JmvbKUSoN.u4uodCSPd8hC.4ZWqwg/6/fCd6gAymk2', 'EMPRESA', NULL, '(19) 3581-5555', 'Porto Ferreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(6, 'Test User', 'candidate_test@example.com', '$2y$10$/Vi1s6Rt7C6OY8cz3fKTMu21AkPJxh8FfPYMqGXZPyRQGAQ06d4F6', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 15:57:43', '2026-04-04 15:57:43'),
(7, 'Matheus Bezerra', 'matheus7gbs@gmail.com', '$2y$10$M6XdDniHNfOsIcg3zQ1IcurBDdB1dra2q706WXMiYHsBrrmjA2qhq', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-09 02:05:36', '2026-04-09 02:05:36'),
(8, 'MATHEUS GABRIEL BEZERRA SANTOS', 'matheusbezerra7gbs@gmail.com', '$2y$10$f5AYYV34ZjH4/DAmLjbbDeRMReB2BnGevnUyyRV7eAbIho5TjATS6', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-09 02:45:56', '2026-04-09 02:45:56'),
(9, 'Test PHPMailer', 'prospectiveblack@deltajohnsons.com', '$2y$10$KKKO0iSsbLrND5fRyMxbPuOoDJBETB0uPieh5K2urbErruqbgY.gG', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 15:28:04', '2026-04-10 15:28:04'),
(10, 'Test PHPMailer 2', 'test_phpmailer_2@deltajohnsons.com', '$2y$10$yj24ljhPZrqBvQnLsndj/uOl7DzBMk/jwt946sIuD0Z.Clsy79.cC', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 15:31:14', '2026-04-10 15:31:14'),
(11, 'Test PHPMailer 3', 'test_phpmailer_3@deltajohnsons.com', '$2y$10$uJY0HVDM87F7wANqulxkL.Y10JlsPl264O6WMruHjCmk7TQKiLt9m', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 15:32:22', '2026-04-10 15:32:22'),
(12, 'MATHEUS GABRIEL BEZERRA SANTOS', 'matthysantos25@gmail.com', '$2y$10$janOspNuylC5HW8iCRN5ReBmMdZRyJjxQkAWeOQ4tt7H8yuf6X.QS', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 01:46:13', '2026-04-11 01:46:13'),
(15, 'j', 'jj@gmail.com', '$2y$10$ranQ4qYyTpot.gp66IPAfeELsl8S/YEQ6QHjMD3DU.0FjCNVUKPQC', 'EMPRESA', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 03:41:03', '2026-04-15 03:41:03'),
(16, 'eu', 'eu@gmail.com', '$2y$10$B1SID84uQ2RskqN88LVype2j2/YQW.B1mkQvnsP15gYWhItzce/gy', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 03:54:28', '2026-04-15 03:54:28'),
(24, 'test', 'softdelete@teste.com', 'hash', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 16:48:31', '2026-04-15 16:48:31', '2026-04-15 16:48:31'),
(29, 'Audit Candidato Editado', 'audit_candidato@teste.com', '$2y$10$5Ac.RmACTzZYWPHofbHLL.P0yTtu5MFRzY.sVkOW/fPJAjhrJlOL.', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 17:19:13', '2026-04-15 17:18:19', '2026-04-15 17:19:13'),
(30, 'Audit Empresa User', 'audit_empresa@teste.com', '$2y$10$d3l0eEHhYuaCsc31E4zQ..jL.rtRu9j8MVGg4xLxciInC/3AynncW', 'EMPRESA', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 17:19:13', '2026-04-15 17:18:38', '2026-04-15 17:19:13'),
(31, 'Admin Teste', 'empresa_teste_v2@gmail.com', '$2y$10$Ms.veaQibkSvIhzjw.dlp.2q4EM4fl95HTBV0gfAt4uvZ1UuPYsq.', 'EMPRESA', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:52:04', '2026-04-18 14:52:04'),
(32, 'Candidate Teste', 'candidate_teste@gmail.com', '$2y$10$xkEYXJyhhHzC40..X7O4reReluyHynsRq/x73sDX.G7oSUML7UE9i', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:59:44', '2026-04-18 14:59:44'),
(33, 'Candidato Teste', 'candidato@gmail.com', '$2y$10$nvtadvE9YvGYXjs2nTDlUeRC/SZSljIr4JUnKZw0a9syR9tPtGHcu', 'CANDIDATO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-19 15:26:40', '2026-04-19 15:26:40'),
(34, 'UI Test User', 'empresa_ui_test5@example.com', '$2y$10$YdjodlVtl/McoZcJ7Vc9HO4DCfi7ox0n9OIrSYUtop29mszrVWi5S', 'EMPRESA', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-19 16:13:34', '2026-04-19 16:13:34'),
(35, 'matheus gabriel bezerra', 'matheusbezerrasantos2015@gmail.com', '$2y$10$xVl4c3x4VPQ3NGOOh2Jd7OHk9tSdbXiJBwWol2rASt/ffQ/AWyM72', 'CANDIDATO', NULL, '', '', '', '', '', '', '', '/backend/uploads/avatares/avatar_35_1777299789.png', NULL, '2026-04-21 15:08:38', '2026-04-27 14:23:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vagas`
--

CREATE TABLE `vagas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `descricao` text NOT NULL,
  `requisitos` text DEFAULT NULL,
  `diferenciais` text DEFAULT NULL,
  `beneficios` text DEFAULT NULL,
  `salario_min` decimal(10,2) DEFAULT NULL,
  `salario_max` decimal(10,2) DEFAULT NULL,
  `tipo_contrato` varchar(50) NOT NULL,
  `tipo_vaga` varchar(50) DEFAULT 'PRESENCIAL',
  `experiencia` varchar(50) DEFAULT 'SEM_EXPERIENCIA',
  `carga_horaria` varchar(100) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT 'Porto Ferreira',
  `estado` varchar(2) DEFAULT 'SP',
  `status` enum('ATIVA','PAUSADA','CONCLUIDA','EXPIRADA') DEFAULT 'ATIVA',
  `data_expiracao` date DEFAULT NULL,
  `publicada_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `vagas`
--

INSERT INTO `vagas` (`id`, `empresa_id`, `titulo`, `cargo`, `descricao`, `requisitos`, `diferenciais`, `beneficios`, `salario_min`, `salario_max`, `tipo_contrato`, `tipo_vaga`, `experiencia`, `carga_horaria`, `area`, `nivel`, `cidade`, `estado`, `status`, `data_expiracao`, `publicada_em`, `created_at`, `updated_at`) VALUES
(1, 2, 'Operador de Forno', 'Operador de Forno', 'Buscamos um profissional dedicado para atuar como Operador de Forno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1800.00, 3300.00, 'PJ', 'HIBRIDO', 'SEM_EXPERIENCIA', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-29 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(2, 3, 'Vendedor Interno', 'Vendedor Interno', 'Buscamos um profissional dedicado para atuar como Vendedor Interno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 2200.00, 3700.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-28 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(3, 4, 'Motorista Categoria D', 'Motorista Categoria D', 'Buscamos um profissional dedicado para atuar como Motorista Categoria D em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3500.00, 5000.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-31 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(4, 4, 'Analista de Logística', 'Analista de Logística', 'Buscamos um profissional dedicado para atuar como Analista de Logística em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 4000.00, 5500.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-29 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(5, 2, 'Designer Gráfico (Cerâmica)', 'Designer Gráfico (Cerâmica)', 'Buscamos um profissional dedicado para atuar como Designer Gráfico (Cerâmica) em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3800.00, 5300.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-31 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(8, 3, 'Gerente Comercial', 'Gerente Comercial', 'Buscamos um profissional dedicado para atuar como Gerente Comercial em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 6000.00, 7500.00, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-07 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(9, 2, 'Auxiliar de Produção', 'Auxiliar de Produção', 'Buscamos um profissional dedicado para atuar como Auxiliar de Produção em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1500.00, 3000.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-13 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(11, 2, 'Operador de Forno - Vaga 11', 'Operador de Forno', 'Buscamos um profissional dedicado para atuar como Operador de Forno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1800.00, 3300.00, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-31 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(12, 3, 'Vendedor Interno - Vaga 12', 'Vendedor Interno', 'Buscamos um profissional dedicado para atuar como Vendedor Interno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 2200.00, 3700.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-18 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(13, 4, 'Motorista Categoria D - Vaga 13', 'Motorista Categoria D', 'Buscamos um profissional dedicado para atuar como Motorista Categoria D em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3500.00, 5000.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-08 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(14, 4, 'Analista de Logística - Vaga 14', 'Analista de Logística', 'Buscamos um profissional dedicado para atuar como Analista de Logística em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 4000.00, 5500.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-09 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(15, 2, 'Designer Gráfico (Cerâmica) - Vaga 15', 'Designer Gráfico (Cerâmica)', 'Buscamos um profissional dedicado para atuar como Designer Gráfico (Cerâmica) em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3800.00, 5300.00, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44 horas semanais', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-15 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(18, 3, 'Gerente Comercial - Vaga 18', 'Gerente Comercial', 'Buscamos um profissional dedicado para atuar como Gerente Comercial em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 6000.00, 7500.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-29 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(19, 2, 'Auxiliar de Produção - Vaga 19', 'Auxiliar de Produção', 'Buscamos um profissional dedicado para atuar como Auxiliar de Produção em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1500.00, 3000.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-20 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(21, 2, 'Operador de Forno - Vaga 21', 'Operador de Forno', 'Buscamos um profissional dedicado para atuar como Operador de Forno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1800.00, 3300.00, 'PJ', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-19 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(22, 3, 'Vendedor Interno - Vaga 22', 'Vendedor Interno', 'Buscamos um profissional dedicado para atuar como Vendedor Interno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 2200.00, 3700.00, 'CLT', 'HIBRIDO', 'SEM_EXPERIENCIA', '44 horas semanais', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-23 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(23, 4, 'Motorista Categoria D - Vaga 23', 'Motorista Categoria D', 'Buscamos um profissional dedicado para atuar como Motorista Categoria D em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3500.00, 5000.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-15 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(24, 4, 'Analista de Logística - Vaga 24', 'Analista de Logística', 'Buscamos um profissional dedicado para atuar como Analista de Logística em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 4000.00, 5500.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-26 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(25, 2, 'Designer Gráfico (Cerâmica) - Vaga 25', 'Designer Gráfico (Cerâmica)', 'Buscamos um profissional dedicado para atuar como Designer Gráfico (Cerâmica) em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3800.00, 5300.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-08 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(28, 3, 'Gerente Comercial - Vaga 28', 'Gerente Comercial', 'Buscamos um profissional dedicado para atuar como Gerente Comercial em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 6000.00, 7500.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-27 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(29, 2, 'Auxiliar de Produção - Vaga 29', 'Auxiliar de Produção', 'Buscamos um profissional dedicado para atuar como Auxiliar de Produção em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1500.00, 3000.00, 'CLT', 'HIBRIDO', '1_A_3_ANOS', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-29 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(31, 2, 'Operador de Forno - Vaga 31', 'Operador de Forno', 'Buscamos um profissional dedicado para atuar como Operador de Forno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1800.00, 3300.00, 'PJ', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-10 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(32, 3, 'Vendedor Interno - Vaga 32', 'Vendedor Interno', 'Buscamos um profissional dedicado para atuar como Vendedor Interno em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 2200.00, 3700.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Júnior', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-21 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(33, 4, 'Motorista Categoria D - Vaga 33', 'Motorista Categoria D', 'Buscamos um profissional dedicado para atuar como Motorista Categoria D em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3500.00, 5000.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Transporte', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-14 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(34, 4, 'Analista de Logística - Vaga 34', 'Analista de Logística', 'Buscamos um profissional dedicado para atuar como Analista de Logística em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 4000.00, 5500.00, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '44 horas semanais', 'Logística', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-23 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(35, 2, 'Designer Gráfico (Cerâmica) - Vaga 35', 'Designer Gráfico (Cerâmica)', 'Buscamos um profissional dedicado para atuar como Designer Gráfico (Cerâmica) em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 3800.00, 5300.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Design', 'Pleno', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-28 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(38, 3, 'Gerente Comercial - Vaga 38', 'Gerente Comercial', 'Buscamos um profissional dedicado para atuar como Gerente Comercial em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 6000.00, 7500.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Vendas', 'Gestão', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-30 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(39, 2, 'Auxiliar de Produção - Vaga 39', 'Auxiliar de Produção', 'Buscamos um profissional dedicado para atuar como Auxiliar de Produção em nossa sede em Porto Ferreira. O profissional será responsável por executar rotinas essenciais de forma proativa e dinâmica.\\n\\nAlém das atividades diárias, o candidato integrará uma equipe colaborativa com foco em alto desempenho. Estamos em franca expansão e oferecemos um ambiente propício ao crescimento profissional, com forte incentivo à qualificação constante.\\n\\nOferecemos estrutura completa de trabalho, política de feedback contínuo e plano de carreira estruturado para aqueles que se destacarem nas metas semestrais.', '- Experiência comprovada na área\\n- Comprometimento com prazos e pontualidade\\n- Postura ética e espírito de equipe\\n- Ensino Médio/Técnico/Superior conforme aplicável', '- Cursos de aprimoramento\\n- Vivência no setor comercial/industrial de Porto Ferreira', '- Vale Transporte\\n- Vale Refeição\\n- Plano de Saúde corporativo\\n- Bônus por metas atingidas', 1500.00, 3000.00, 'CLT', 'PRESENCIAL', '1_A_3_ANOS', '44 horas semanais', 'Produção', 'Operacional', 'Porto Ferreira', 'SP', 'ATIVA', '2026-05-04', '2026-03-12 19:23:01', '2026-04-04 14:23:05', '2026-04-04 14:23:05'),
(46, 6, 'jj', 'jj', 'jjjjjjjjjjjjjjjjjjjjjjjjjjj\njjjjjjjjjjjjjjjjjjjjjjjjjjj\njjjjjjjjjjjjjjjjjjjjjjjjjjj\njjjjjjjjjjjjjjjjjjjjjjjjjjj', 'jjjjjjjjjjjj', 'jjjjjjjjjjjjjjjj', 'jjj', 0.48, 1.03, 'PJ', 'REMOTO', 'SEM_EXPERIENCIA', '40h', NULL, NULL, 'Porto Ferreira', 'SP', 'ATIVA', NULL, '2026-04-15 03:53:26', '2026-04-15 03:53:26', '2026-04-15 03:53:26'),
(55, 13, 'Audit - Motorista de Entrega EDITADO', 'Motorista Sênior', 'Descrição editada via auditoria — conteúdo único', 'CNH categoria C, 2 anos de experiência, disponibilidade', NULL, NULL, 3000.00, 4000.00, 'CLT', 'PRESENCIAL', 'PLENO', NULL, 'Logística', NULL, 'Porto Ferreira', 'SP', 'PAUSADA', '2026-04-18', '2026-04-15 17:18:44', '2026-04-15 17:18:44', '2026-04-15 17:19:13'),
(57, 14, 'Desenvolvedor Full Stack TESTE EDITADO', 'Desenvolvedor', 'Vaga de teste para automa', 'Experiencia com PHP e MySQL. Dominio de JS.', 'Conhecimento em React e Node.js.', 'VT, VR, Plano de Saude, Seguro de Vida.', 5000.00, 8000.00, 'CLT', 'PRESENCIAL', 'SENIOR', '40h semanais', NULL, NULL, 'Porto Ferreira', 'SP', 'ATIVA', NULL, '2026-04-18 14:54:25', '2026-04-18 14:54:25', '2026-04-18 14:56:10'),
(58, 14, 'Vaga para Excluir', 'Teste', 'Teste de exclusao.', 'Nenhum.', '', '', NULL, NULL, 'CLT', 'PRESENCIAL', 'SEM_EXPERIENCIA', '', NULL, NULL, 'Porto Ferreira', 'SP', 'ATIVA', NULL, '2026-04-18 15:08:04', '2026-04-18 15:08:04', '2026-04-18 15:08:04');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vaga_id` (`vaga_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`vaga_id`),
  ADD KEY `vaga_id` (`vaga_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `vagas`
--
ALTER TABLE `vagas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `vagas`
--
ALTER TABLE `vagas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `vagas`
--
ALTER TABLE `vagas`
  ADD CONSTRAINT `vagas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

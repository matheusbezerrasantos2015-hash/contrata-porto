# ContrataPorto

> Plataforma digital de empregos 100% local para Porto Ferreira/SP

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES_Modules-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Railway](https://img.shields.io/badge/Deploy-Railway-0B0D0E?style=flat-square&logo=railway&logoColor=white)
![Cloudinary](https://img.shields.io/badge/Storage-Cloudinary-3448C5?style=flat-square)
![License](https://img.shields.io/badge/Licença-Acadêmica-blue?style=flat-square)

---

## Sumário

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Arquitetura](#arquitetura)
- [Stack Tecnológica](#stack-tecnológica)
- [Estrutura do Repositório](#estrutura-do-repositório)
- [Banco de Dados](#banco-de-dados)
- [Como Rodar Localmente](#como-rodar-localmente)
- [Deploy em Produção](#deploy-em-produção)
- [Equipe e Papéis Scrum](#equipe-e-papéis-scrum)
- [Metodologia Ágil](#metodologia-ágil)
- [Contexto Acadêmico](#contexto-acadêmico)

---

## Sobre o Projeto

Porto Ferreira é um município com forte vocação industrial — especialmente no setor cerâmico — e um comércio local ativo. Mesmo assim, a intermediação de empregos ainda ocorre de forma majoritariamente manual: vagas são divulgadas pelo PAT (Posto de Atendimento ao Trabalhador) em grupos de WhatsApp, e candidatos precisam comparecer presencialmente para se cadastrar e retirar carta de encaminhamento.

O **ContrataPorto** resolve esse problema com uma plataforma web completa, gratuita e acessível pelo celular, que conecta candidatos e empresas da região em um ambiente digital sem burocracia.

**Custo de operação: R$ 27,00/mês** (Railway $5 USD + Cloudinary gratuito) — viável para um projeto de impacto social local.

---

## Funcionalidades

### Para Candidatos
- Cadastro com verificação de e-mail obrigatória (código de 6 dígitos, expiração 15 min)
- Busca de vagas com filtros por área, experiência, tipo de contrato e faixa salarial
- Candidatura com mensagem personalizada e upload de currículo PDF (até 5 MB via Cloudinary)
- Dashboard com acompanhamento em tempo real do status de cada candidatura
- Favoritos — salvar e gerenciar vagas de interesse
- Tour interativo Shepherd.js para novos usuários
- Recuperação de senha por e-mail (token 64 hex chars, expiração 1 hora)
- Contato direto com empresa via WhatsApp
- PWA instalável com Service Worker para cache offline

### Para Empresas
- Publicação de vagas com dados completos: tipo de contrato, modalidade, faixa salarial, nível
- Dashboard com lista de vagas e **drawer lateral** de candidatos por vaga
- Visualização e download de currículos PDF diretamente do Cloudinary
- Atualização de status dos candidatos (em análise / aprovado / recusado)
- Paginação de vagas: 12 por página via stored procedure

### Automações
- Notificações automáticas por e-mail via **Brevo API** (boas-vindas, candidatura, status, senha)
- Envio assíncrono de e-mails via `register_shutdown_function`
- Expiração automática de vagas concluídas após 3 dias (cron diário)

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend (Vanilla JS)                  │
│  ES Modules · CSS Custom Properties · PWA · Shepherd.js  │
└──────────────────────┬──────────────────────────────────┘
                       │ REST API (JSON)
┌──────────────────────▼──────────────────────────────────┐
│                  Backend (PHP 8.2 MVC)                    │
│  Router → Controllers → Services → Models → PDO          │
│  JWT Auth · AuthMiddleware · RateLimiter · Mailer        │
└──────────┬───────────────────────────┬───────────────────┘
           │                           │
┌──────────▼──────────┐   ┌────────────▼──────────────┐
│    MySQL 8.0         │   │      Cloudinary            │
│  4 Stored Procedures │   │  Upload autenticado SHA1   │
│  PDO + Prepared Stmt │   │  URLs públicas permanentes │
└─────────────────────┘   └───────────────────────────┘
```

O backend implementa **MVC sem framework** — todas as camadas (Router, Controller, Service, Model) foram escritas do zero, sem dependência de Laravel, Symfony ou similar.

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2 — MVC sem framework |
| Frontend | Vanilla JS com ES Modules |
| Banco de Dados | MySQL 8.0 — PDO + Prepared Statements + Stored Procedures |
| Autenticação | JWT (payload: `role`, `id`, `empresa_id`) — TTL 7.200s |
| Storage de PDFs | Cloudinary (upload SHA1 autenticado) |
| E-mail transacional | Brevo API (assíncrono via `register_shutdown_function`) |
| Tour interativo | Shepherd.js |
| PWA | Service Worker + Web App Manifest |
| Deploy | Railway (CI/CD automático via push) |

---

## Estrutura do Repositório

```
contrataporto/
├── backend/
│   ├── config/           # app.php, database.php, env.php
│   ├── controllers/      # AuthController, JobController, ApplicationController...
│   ├── core/             # Router, JWTService, Mailer, CloudinaryUploader, RateLimiter
│   ├── middlewares/      # AuthMiddleware
│   ├── models/           # User, Job, Application, Company, Favorite
│   ├── routes/           # api.php
│   ├── scripts/          # cron_expirar_vagas.php
│   ├── services/         # AuthService, ApplicationService, JobService
│   └── templates/emails/ # Templates HTML para cada evento de e-mail
│
├── frontend/
│   ├── assets/           # Logo, favicon
│   ├── components/       # header.html, footer.html
│   ├── css/              # base, components, layout, pages, style
│   ├── js/               # Módulos ES: api, auth, dashboard, jobs, tour...
│   └── pages/            # index, login, cadastro, job, dashboard, settings...
│
├── .env.example
├── .htaccess
└── README.md
```

---

## Banco de Dados

O banco MySQL 8.0 utiliza **4 Stored Procedures** para operações críticas:

| Stored Procedure | Finalidade |
|---|---|
| `sp_candidatar_vaga` | Candidatura com atomicidade e validação de duplicidade |
| `sp_atualizar_status_candidatura` | Atualização de status com verificação de autorização |
| `sp_listar_vagas_ativas` | Listagem paginada com busca textual e filtros combinados |
| `sp_expirar_vagas_concluidas` | Expiração automática de vagas após 3 dias (via cron) |

Tabelas principais: `users`, `companies`, `jobs`, `applications`, `favorites`, `password_resets`, `email_verifications`

---

## Como Rodar Localmente

### Pré-requisitos

- PHP 8.2+
- MySQL 8.0+
- Composer
- Servidor HTTP com suporte a `.htaccess` (Apache ou Nginx com rewrite)

### Instalação

```bash
git clone https://github.com/seu-usuario/contrataporto.git
cd contrataporto

# Instalar dependências PHP
cd backend
composer install

# Configurar variáveis de ambiente
cp .env.example .env
# Edite .env com suas credenciais MySQL, JWT secret, Cloudinary e Brevo API Key

# Importar banco de dados
mysql -u root -p < database/schema.sql

# Iniciar servidor (desenvolvimento)
php -S localhost:8000 -t backend/public
```

O frontend é servido estático — basta abrir `frontend/pages/index.html` ou configurar o servidor para servir a pasta `frontend/` na raiz.

### Variáveis de Ambiente Necessárias

```env
DB_HOST=localhost
DB_NAME=contrataporto
DB_USER=seu_usuario
DB_PASS=sua_senha

JWT_SECRET=seu_jwt_secret

CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=

BREVO_API_KEY=
BREVO_SENDER_EMAIL=
BREVO_SENDER_NAME=ContrataPorto
```

---

## Deploy em Produção

O projeto roda no **Railway** com CI/CD automático: cada `git push` na branch `main` dispara build e deploy automaticamente.

- **Backend**: PHP no Railway com Nixpacks
- **Banco**: MySQL provisionado pelo Railway
- **Frontend**: servido junto ao backend via `.htaccess`
- **Cron**: `cron_expirar_vagas.php` configurado via Railway Cron Jobs

---

## Equipe e Papéis Scrum

| Papel Scrum | Integrante | Responsabilidades |
|---|---|---|
| **Product Owner & Scrum Master** | **Matheus Gabriel Bezerra Santos** | Definição e priorização do Product Backlog; arquitetura geral do sistema; facilitação de todos os eventos Scrum; remoção de impedimentos; decisões técnicas de stack e infraestrutura; gestão do repositório GitHub e controle de branches; integração das entregas das Sprints; validação dos incrementos. |
| Developer | André Luiz Rodrigues P. da Silva | Implementação de funcionalidades conforme backlog priorizado; testes manuais; participação em planning e review. |
| Developer | Lucas Galvão | Implementação de funcionalidades conforme backlog priorizado; testes manuais; participação em planning e review. |

> **Matheus atuou simultaneamente como Product Owner e Scrum Master** — papel combinado adotado por decisão da equipe, compatível com times pequenos segundo o Scrum Guide 2020. Além da gestão ágil, foi responsável pela arquitetura do sistema, definição da stack, estrutura MVC, modelagem do banco de dados, configuração do deploy no Railway e integração de todos os serviços externos (Cloudinary, Brevo API, JWT).

---

## Metodologia Ágil

O projeto foi desenvolvido em **5 Sprints de uma semana**, com os eventos Scrum aplicados da seguinte forma:

**Sprint Planning** — realizado no início de cada Sprint com seleção dos itens do backlog, definição do Sprint Goal e decomposição em tarefas técnicas com estimativas de esforço.

**Daily Scrum** — formato assíncrono via WhatsApp, com cada integrante respondendo diariamente: o que fez, o que planeja fazer, e eventuais impedimentos.

**Sprint Review** — apresentação do incremento no ambiente de produção do Railway e ajuste do backlog para a próxima iteração.

**Sprint Retrospective** — identificação de melhorias de processo. Exemplos implementados: commits atômicos, branches por feature, padronização de comentários de código.

### Incrementos por Sprint

| Sprint | Principais Entregas |
|---|---|
| Sprint 1 | Autenticação JWT, cadastro de usuários e empresas |
| Sprint 2 | Candidatura com currículo PDF via Cloudinary |
| Sprint 3 | Notificações por e-mail e dashboard com drawer lateral |
| Sprint 4 | Recuperação de senha e tour guiado Shepherd.js |
| Sprint 5 | Verificação de e-mail obrigatória, seed de dados, responsividade mobile |

### Definition of Done

Um item é considerado concluído quando:
- Funcionalidade implementada e testada no Chrome e Firefox
- Endpoint respondendo com status HTTP correto e payload JSON esperado
- Sem erros no console do navegador (DevTools)
- Dados verificados diretamente no MySQL via DBeaver/Railway
- Deploy realizado com sucesso no Railway

---

## Contexto Acadêmico

Projeto Integrador do **2º Semestre** do curso de **Tecnologia em Desenvolvimento de Software Multiplataforma (DSM)** — FATEC Porto Ferreira, 2026.

Trabalho interdisciplinar apresentado como requisito parcial para avaliação das disciplinas do semestre.

---

*ContrataPorto v4.0 — FATEC Porto Ferreira · DSM · 2026*

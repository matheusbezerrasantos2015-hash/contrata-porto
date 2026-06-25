# ContrataPorto

Plataforma de vagas de emprego para Porto Ferreira/SP.

## Stack

- **Backend**: Laravel 11 + Sanctum + MySQL
- **Frontend**: React 18 + Vite + TailwindCSS
- **Deploy**: Railway (Docker multi-stage)

## Estrutura

- `/app` — Laravel (Models, Controllers, Services)
- `/frontend` — React + Vite
- `/docker` — Nginx config + entrypoint
- `/database/migrations` — Migrations Laravel

## Requisitos locais

- PHP 8.2+
- Composer
- Node 20+
- MySQL

## Rodando localmente

### Backend

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

## Deploy

O deploy é feito automaticamente via Railway ao fazer push no branch main.
Fora do horário de pico (00h–12h horário de Brasília no plano gratuito).

## Variáveis de ambiente

Ver `.env.example` para todas as variáveis necessárias.

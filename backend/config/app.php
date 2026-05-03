<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
loadEnv(realpath(__DIR__ . '/../../.env'));

$env = getenv('APP_ENV') ?: 'development';
$isProduction = $env === 'production';

return [
    'app' => [
        'name' => 'ContrataPorto',
        'env' => $env,
        'debug' => !$isProduction,
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost:8000',
        'frontend_url' => getenv('FRONTEND_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'Contrata_Porto',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'jwt_secret' => getenv('JWT_SECRET') ?: 'change-this-secret-in-production',
        'jwt_ttl_seconds' => (int) (getenv('JWT_TTL_SECONDS') ?: 7200),
        'allowed_origins' => array_filter(array_map('trim', explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost'))),
        'session_name' => 'contrataporto_session',
    ],
];

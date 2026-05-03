<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
loadEnv(realpath(__DIR__ . '/../../.env'));

function env(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

$env = env('APP_ENV', 'development');
$isProduction = $env === 'production';

return [
    'app' => [
        'name' => 'ContrataPorto',
        'env' => $env,
        'debug' => !$isProduction,
        'base_url' => env('APP_BASE_URL', 'http://localhost:8000'),
        'frontend_url' => env('FRONTEND_URL', 'http://localhost'),
        'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    ],
    'database' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'railway'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'jwt_secret' => env('JWT_SECRET', 'change-this-secret'),
        'jwt_ttl_seconds' => (int) env('JWT_TTL_SECONDS', 7200),
        'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost')))),
        'session_name' => 'contrataporto_session',
    ],
];
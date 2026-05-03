<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
loadEnv(realpath(__DIR__ . '/../../.env'));

function getEnv2(string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

$env = getEnv2('APP_ENV', 'development');
$isProduction = $env === 'production';

return [
    'app' => [
        'name' => 'ContrataPorto',
        'env' => $env,
        'debug' => !$isProduction,
        'base_url' => getEnv2('APP_BASE_URL', 'http://localhost:8000'),
        'frontend_url' => getEnv2('FRONTEND_URL', 'http://localhost'),
        'timezone' => getEnv2('APP_TIMEZONE', 'America/Sao_Paulo'),
    ],
    'database' => [
        'host' => getEnv2('DB_HOST', '127.0.0.1'),
        'port' => getEnv2('DB_PORT', '3306'),
        'name' => getEnv2('DB_NAME', 'railway'),
        'user' => getEnv2('DB_USER', 'root'),
        'pass' => getEnv2('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'jwt_secret' => getEnv2('JWT_SECRET', 'change-this-secret'),
        'jwt_ttl_seconds' => (int) getEnv2('JWT_TTL_SECONDS', 7200),
        'allowed_origins' => array_filter(array_map('trim', explode(',', getEnv2('CORS_ALLOWED_ORIGINS', 'http://localhost')))),
        'session_name' => 'contrataporto_session',
    ],
];
<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['app']['timezone']);

set_exception_handler(static function (Throwable $exception) use ($config): void {
    error_log('[API_EXCEPTION] ' . $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
    
    $message = $config['app']['debug'] ? $exception->getMessage() : 'Internal Server Error';
    Response::json(false, $message, null, 500);
});

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $config['security']['allowed_origins'];
if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Vary: Origin');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'");
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';

$router = new Router();
require __DIR__ . '/../routes/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($uri, PHP_URL_PATH) ?: '/';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$basePath = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim($basePath, '/');

if (str_starts_with($requestPath, $scriptName)) {
    $cleanUri = substr($requestPath, strlen($scriptName));
} elseif ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $cleanUri = substr($requestPath, strlen($basePath));
} else {
    $cleanUri = $requestPath;
}

$cleanUri = '/' . ltrim($cleanUri, '/');

if ($config['app']['debug']) {
    error_log("[ROUTER_DEBUG] Method: {$_SERVER['REQUEST_METHOD']} | Original URI: {$uri} | Clean URI: {$cleanUri}");
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $cleanUri);
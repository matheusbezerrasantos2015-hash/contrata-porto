<?php

/**
 * Router para o servidor embutido do PHP (php -S).
 * Redireciona rotas legadas e encaminha API + SPA React.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 1. API Laravel
if (str_starts_with($uri, '/api')) {
    require __DIR__ . '/public/index.php';
    return true;
}

// 2. Assets do Laravel (emails)
if (str_starts_with($uri, '/frontend/assets/')) {
    $file = __DIR__ . '/public' . $uri;
    if (is_file($file)) {
        return false;
    }
}

// 3. Redirecionamentos do frontend legado → SPA React
$legacyRedirects = [
    '/frontend/pages/index.html'             => '/',
    '/frontend/pages/jobs.html'              => '/vagas',
    '/frontend/pages/job.html'               => '/vagas',
    '/frontend/pages/login.html'             => '/login',
    '/frontend/pages/cadastro.html'          => '/cadastro',
    '/frontend/pages/favorites.html'         => '/favoritos',
    '/frontend/pages/candidate-dashboard.html' => '/dashboard/candidato',
    '/frontend/pages/company-dashboard.html'   => '/dashboard/empresa',
    '/frontend/pages/candidate-settings.html'  => '/settings/candidato',
    '/frontend/pages/company-settings.html'    => '/settings/empresa',
    '/frontend/pages/forgot-password.html'     => '/esqueci-senha',
    '/frontend/pages/reset-password.html'      => '/reset-senha',
    '/frontend/pages/verify-email.html'        => '/verificar-email',
];

if (isset($legacyRedirects[$uri])) {
    header('Location: ' . $legacyRedirects[$uri], true, 301);
    exit;
}

if (str_starts_with($uri, '/frontend/pages/')) {
    header('Location: /', true, 301);
    exit;
}

// 4. SPA React (build em public/app/)
$spaRoot = __DIR__ . '/public/app';
$spaFile = $spaRoot . $uri;

if ($uri !== '/' && is_file($spaFile)) {
    return false;
}

$indexFile = $spaRoot . '/index.html';
if (is_file($indexFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($indexFile);
    return true;
}

http_response_code(404);
echo '404 Not Found';
return true;

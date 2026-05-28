<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// Segurança: bloquear execução de PHP em uploads
if (strpos($uri, '/uploads/') !== false && 
    pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// 1. Se o arquivo físico existe (css, js, imagens), serve ele direto
if ($uri !== '/' && file_exists($file)) {
    return false;
}

// 2. Se a URL começa com /api, manda para o index.php do backend
if (strpos($uri, '/api') === 0) {
    // Ajusta o SCRIPT_NAME para o roteador do backend entender
    $_SERVER['SCRIPT_NAME'] = '/backend/public/index.php';
    require __DIR__ . '/backend/public/index.php';
    return;
}

// 3. Se for a raiz, serve o index.html do frontend
if ($uri === '/' || $uri === '/index.html') {
    require __DIR__ . '/frontend/pages/index.html';
    return;
}

// 4. Se for qualquer outra página do frontend (ex: /jobs.html)
$frontendFile = __DIR__ . '/frontend/pages' . $uri;
if (file_exists($frontendFile)) {
    require $frontendFile;
    return;
}

// 5. Caso contrário, serve o 404 do frontend
if (file_exists(__DIR__ . '/frontend/pages/404.html')) {
    require __DIR__ . '/frontend/pages/404.html';
    return;
}

return false;

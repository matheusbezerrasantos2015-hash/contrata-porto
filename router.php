<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Se o arquivo existe, serve direto
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Se começa com /backend/public/index.php, redireciona para o index
if (strpos($uri, '/backend/public/index.php') !== false) {
    require __DIR__ . '/backend/public/index.php';
    exit;
}

return false;
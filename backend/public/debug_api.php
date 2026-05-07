<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getConnection();
    
    $tables = ['users', 'empresas', 'vagas', 'applications'];
    $stats = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
            $stats[$table] = $stmt->fetch()['total'];
        } catch (Exception $e) {
            $stats[$table] = "ERRO: " . $e->getMessage();
        }
    }

    // Verifica a primeira vaga e sua empresa
    $vagaExemplo = $db->query("SELECT id, titulo, empresa_id, status FROM vagas LIMIT 1")->fetch();
    
    echo json_encode([
        'success' => true,
        'database_stats' => $stats,
        'vaga_exemplo' => $vagaExemplo,
        'routing_debug' => [
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'PHP_SELF'    => $_SERVER['PHP_SELF'] ?? 'N/A',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        ],
        'php_info' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

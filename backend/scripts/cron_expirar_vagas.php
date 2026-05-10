<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

if (PHP_SAPI !== 'cli') {
    $token = $_GET['token'] ?? '';
    $esperado = getEnv2('CRON_SECRET', '');
    if (empty($esperado) || $token !== $esperado) {
        http_response_code(403);
        exit('Forbidden');
    }
}

$db = Database::getConnection();
$stmt = $db->prepare("CALL sp_expirar_vagas_concluidas(@afetadas)");
$stmt->execute();
$stmt->closeCursor();
$qtd = (int) $db->query("SELECT @afetadas")->fetchColumn();
error_log("[CRON] sp_expirar_vagas_concluidas: {$qtd} vagas expiradas em " . date('Y-m-d H:i:s'));
echo "OK: {$qtd} vagas expiradas.\n";

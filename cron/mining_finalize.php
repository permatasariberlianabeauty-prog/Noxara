<?php
/**
 * NOXARA Cron - Finalize mining yang sudah selesai 2 jam
 * Schedule: * * * * * (setiap menit)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/notification.php';
require_once __DIR__ . '/../includes/mining.php';

$start = microtime(true);
$count = 0;

// Find mining logs that started 2+ hours ago and still status 'mining'
$result = db()->query("SELECT id FROM mining_logs WHERE status = 'mining' AND started_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)");

while ($row = $result->fetch_assoc()) {
    if (finalize_mining($row['id'])) {
        $count++;
    }
}

$elapsed = round(microtime(true) - $start, 4);

// Log
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('mining_finalize', 'success', ?, ?, ?)");
$msg = "Finalized {$count} mining sessions";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] mining_finalize: {$count} finalized ({$elapsed}s)\n";

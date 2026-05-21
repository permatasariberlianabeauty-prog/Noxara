<?php
/**
 * NOXARA Cron - Mark expired topups
 * Schedule: * * * * * (setiap menit)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$start = microtime(true);

$result = db()->query("UPDATE topups SET status = 'expired' WHERE status = 'pending' AND expires_at < NOW()");
$count = db()->affected_rows;

$elapsed = round(microtime(true) - $start, 4);
if ($count > 0) {
    $stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('topup_expire', 'success', ?, ?, ?)");
    $msg = "Expired: {$count} topups";
    $stmt->bind_param('sid', $msg, $count, $elapsed);
    $stmt->execute();
    $stmt->close();
}

echo "[" . date('Y-m-d H:i:s') . "] topup_expire: {$count} ({$elapsed}s)\n";

<?php
/**
 * NOXARA Cron - Reset daily missions + cleanup expired vouchers
 * Schedule: 5 0 * * * (00:05 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$start = microtime(true);
$today = date('Y-m-d');

// Quest progress auto-creates today entries on access, no explicit reset needed
// But mark expired vouchers
$expired = db()->query("UPDATE vouchers SET status = 'expired' WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at < NOW()");
$expired_count = db()->affected_rows;

// Clean old password reset tokens (older than 24h)
db()->query("DELETE FROM password_resets WHERE expires_at < NOW() AND used = 0");

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('reset_missions', 'success', ?, ?, ?)");
$msg = "Expired vouchers: {$expired_count}";
$stmt->bind_param('sid', $msg, $expired_count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] reset_missions: expired_vouchers={$expired_count} ({$elapsed}s)\n";

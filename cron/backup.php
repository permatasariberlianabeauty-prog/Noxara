<?php
/**
 * NOXARA Cron - Database backup
 * Schedule: 0 2 * * * (02:00 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/backup.php';

$start = microtime(true);
$result = create_backup();
$elapsed = round(microtime(true) - $start, 4);

$status = $result['success'] ? 'success' : 'error';
$msg = $result['success'] ? 'Backup: ' . $result['filename'] : 'Backup failed: ' . ($result['message'] ?? 'unknown');

$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('backup', ?, ?, 1, ?)");
$stmt->bind_param('ssd', $status, $msg, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] backup: {$msg} ({$elapsed}s)\n";

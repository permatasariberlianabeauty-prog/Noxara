<?php
/**
 * NOXARA Cron - Poll Cashify for pending topups (backup)
 * Schedule: */5 * * * * (setiap 5 menit)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/topup.php';
require_once __DIR__ . '/../includes/vip.php';
require_once __DIR__ . '/../includes/notification.php';
require_once __DIR__ . '/../includes/activity_feed.php';

$start = microtime(true);
$count = 0;

$result = db()->query("SELECT * FROM topups WHERE status = 'pending' AND transaction_id IS NOT NULL AND expires_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

while ($topup = $result->fetch_assoc()) {
    $check = cashify_check_status($topup['transaction_id']);
    if (isset($check['data']['status']) && $check['data']['status'] === 'paid') {
        if (process_topup_paid($topup['id'])) {
            $count++;
        }
    }
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('topup_check', 'success', ?, ?, ?)");
$msg = "Paid topups found: {$count}";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] topup_check: {$count} paid ({$elapsed}s)\n";

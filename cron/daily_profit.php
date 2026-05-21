<?php
/**
 * NOXARA Cron - Daily profit processing (backup for mining_finalize)
 * Also handles cashback day (Saturday/Sunday)
 * Schedule: 0 1 * * * (01:00 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/notification.php';

$start = microtime(true);
$day_of_week = date('w'); // 0=Sun, 6=Sat
$yesterday = date('Y-m-d', strtotime('-1 day'));
$count = 0;

// Saturday cashback (5% of topup yesterday, max 5000)
if ($day_of_week == 6) { // Saturday
    $pct = (float)get_setting('cashback_saturday_percent', '5');
    $max = (float)get_setting('cashback_saturday_max', '5000');
    
    $result = db()->query("SELECT user_id, SUM(total_amount) as total FROM topups WHERE status = 'paid' AND DATE(paid_at) = '{$yesterday}' GROUP BY user_id");
    while ($row = $result->fetch_assoc()) {
        $cashback = min($max, round((float)$row['total'] * $pct / 100));
        if ($cashback > 0) {
            credit_balance($row['user_id'], $cashback, 'Cashback Sabtu', 'cashback', 0);
            record_transaction($row['user_id'], 'bonus', $cashback, 0, 'Cashback Sabtu ' . format_rupiah($cashback));
            $count++;
        }
    }
}

// Sunday cashback (5% of WD net yesterday, max 5000)
if ($day_of_week == 0) { // Sunday
    $pct = (float)get_setting('cashback_sunday_percent', '5');
    $max = (float)get_setting('cashback_sunday_max', '5000');
    
    $result = db()->query("SELECT user_id, SUM(net_amount) as total FROM withdrawals WHERE status = 'approved' AND DATE(processed_at) = '{$yesterday}' GROUP BY user_id");
    while ($row = $result->fetch_assoc()) {
        $cashback = min($max, round((float)$row['total'] * $pct / 100));
        if ($cashback > 0) {
            credit_balance($row['user_id'], $cashback, 'Cashback Minggu', 'cashback', 0);
            record_transaction($row['user_id'], 'bonus', $cashback, 0, 'Cashback Minggu ' . format_rupiah($cashback));
            $count++;
        }
    }
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('daily_profit', 'success', ?, ?, ?)");
$msg = "Cashback processed: {$count}";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] daily_profit: cashback={$count} ({$elapsed}s)\n";

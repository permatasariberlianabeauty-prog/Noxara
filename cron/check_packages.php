<?php
/**
 * NOXARA Cron - Check packages that have completed their duration
 * Schedule: */30 * * * * (setiap 30 menit)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/notification.php';

$start = microtime(true);
$count = 0;

// Packages expired (30 days passed)
$result = db()->query("SELECT id, user_id, paid_from_balance, product_id FROM user_products WHERE status = 'active' AND expires_at <= NOW()");

while ($pkg = $result->fetch_assoc()) {
    $user_id = $pkg['user_id'];
    $capital = (float)$pkg['paid_from_balance'];
    
    // Mark completed
    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("UPDATE user_products SET status = 'completed', completed_at = ? WHERE id = ?");
    $stmt->bind_param('si', $now, $pkg['id']);
    $stmt->execute();
    $stmt->close();
    
    // Return capital (paid_from_balance) to balance
    if ($capital > 0) {
        credit_balance($user_id, $capital, 'Pengembalian modal paket selesai', 'capital_return', $pkg['id']);
        record_transaction($user_id, 'capital_return', $capital, 0, 'Modal kembali - paket selesai', (string)$pkg['id']);
    }
    
    notify_user($user_id, 'Paket Selesai', 'Paket Anda telah selesai dan modal ' . format_rupiah($capital) . ' dikembalikan.', 'system');
    $count++;
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('check_packages', 'success', ?, ?, ?)");
$msg = "Completed {$count} packages";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] check_packages: {$count} completed ({$elapsed}s)\n";

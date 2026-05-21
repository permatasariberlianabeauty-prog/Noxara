<?php
/**
 * NOXARA Cron - Special events (birthday bonus, anniversary)
 * Schedule: 0 0 * * * (00:00 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/notification.php';

$start = microtime(true);
$today_md = date('m-d');
$count = 0;

// Birthday bonus: Rp 2.000 for users with birthday today
$result = db()->query("SELECT id, username FROM users WHERE birthday IS NOT NULL AND DATE_FORMAT(birthday, '%m-%d') = '{$today_md}' AND status = 'active'");

while ($user = $result->fetch_assoc()) {
    // Check if already claimed this year
    $year = date('Y');
    $check = db()->query("SELECT id FROM transactions WHERE user_id = {$user['id']} AND type = 'bonus' AND description LIKE 'Birthday bonus%' AND YEAR(created_at) = {$year}")->num_rows;
    
    if ($check === 0) {
        $bonus = 2000;
        credit_balance($user['id'], $bonus, "Birthday bonus {$year}", 'birthday', $user['id']);
        record_transaction($user['id'], 'bonus', $bonus, 0, "Birthday bonus {$year}");
        notify_user($user['id'], 'Selamat Ulang Tahun!', 'Bonus Rp 2.000 untuk Anda. Semoga sukses selalu!', 'bonus');
        $count++;
    }
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('special_events', 'success', ?, ?, ?)");
$msg = "Birthday bonuses: {$count}";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] special_events: birthday={$count} ({$elapsed}s)\n";

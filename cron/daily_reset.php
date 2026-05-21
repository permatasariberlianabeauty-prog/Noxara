<?php
/**
 * NOXARA Cron - Daily reset mining + advance days_claimed for missed
 * Schedule: 1 0 * * * (00:01 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$start = microtime(true);
$yesterday = date('Y-m-d', strtotime('-1 day'));
$count = 0;

// Find active packages that did NOT mine yesterday (missed)
$result = db()->query("SELECT id, user_id FROM user_products WHERE status = 'active' AND (last_mining_date IS NULL OR last_mining_date < '{$yesterday}')");

while ($pkg = $result->fetch_assoc()) {
    // Advance days_claimed (day passes without profit)
    $stmt = db()->prepare("UPDATE user_products SET days_claimed = days_claimed + 1, mining_state = 'idle' WHERE id = ?");
    $stmt->bind_param('i', $pkg['id']);
    $stmt->execute();
    $stmt->close();
    
    // Log missed
    $stmt = db()->prepare("INSERT IGNORE INTO mining_logs (user_id, user_product_id, profit_amount, status, mining_date) VALUES (?, ?, 0, 'missed', ?)");
    $stmt->bind_param('iis', $pkg['user_id'], $pkg['id'], $yesterday);
    $stmt->execute();
    $stmt->close();
    
    $count++;
}

// Reset mining_state to idle for packages that mined yesterday (state = done)
db()->query("UPDATE user_products SET mining_state = 'idle' WHERE status = 'active' AND mining_state = 'done'");

// Auto-Mine for VIP 3 users
$vip3 = db()->query("SELECT up.id, up.user_id FROM user_products up JOIN users u ON u.id = up.user_id WHERE up.status = 'active' AND u.vip_level = 3 AND u.auto_mine = 1 AND up.mining_state = 'idle'");
$auto_count = 0;
while ($pkg = $vip3->fetch_assoc()) {
    require_once __DIR__ . '/../includes/mining.php';
    $r = start_mining($pkg['user_id'], $pkg['id']);
    if ($r['success']) $auto_count++;
}

// Reset mining streak for users who missed
db()->query("UPDATE users SET mining_streak = 0 WHERE id IN (SELECT DISTINCT user_id FROM user_products WHERE status = 'active' AND last_mining_date < '{$yesterday}')");

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('daily_reset', 'success', ?, ?, ?)");
$msg = "Missed: {$count}, Auto-mine VIP3: {$auto_count}";
$stmt->bind_param('sid', $msg, $count, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] daily_reset: missed={$count}, auto_mine={$auto_count} ({$elapsed}s)\n";

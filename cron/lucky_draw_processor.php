<?php
/**
 * NOXARA Cron - Lucky Draw winner picker
 * Schedule: 10 0 * * * (00:10 setiap hari)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ledger.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/notification.php';

$start = microtime(true);
$today = date('Y-m-d');
$drawn = 0;

// Find draws that ended today or before and not yet drawn
$result = db()->query("SELECT * FROM lucky_draws WHERE status = 'active' AND end_date <= '{$today}' AND winner_1 IS NULL");

while ($draw = $result->fetch_assoc()) {
    $draw_id = $draw['id'];
    
    // Get all tickets, pick 3 random winners
    $tickets = db()->query("SELECT DISTINCT user_id FROM lucky_draw_tickets WHERE lucky_draw_id = {$draw_id} ORDER BY RAND() LIMIT 3")->fetch_all(MYSQLI_ASSOC);
    
    if (count($tickets) < 1) continue;
    
    $winners = [null, null, null];
    $prizes = [$draw['prize_1'], $draw['prize_2'], $draw['prize_3']];
    
    for ($i = 0; $i < min(3, count($tickets)); $i++) {
        $winners[$i] = $tickets[$i]['user_id'];
        $prize = (float)$prizes[$i];
        if ($prize > 0 && $winners[$i]) {
            credit_balance($winners[$i], $prize, 'Lucky Draw #' . $draw_id . ' Juara ' . ($i+1), 'lucky_draw', $draw_id);
            record_transaction($winners[$i], 'bonus', $prize, 0, 'Lucky Draw Winner #' . ($i+1));
            notify_user($winners[$i], 'Selamat! Lucky Draw Winner!', 'Anda memenangkan Lucky Draw dan mendapat ' . format_rupiah($prize), 'bonus');
        }
    }
    
    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("UPDATE lucky_draws SET status = 'completed', winner_1 = ?, winner_2 = ?, winner_3 = ?, drawn_at = ? WHERE id = ?");
    $stmt->bind_param('iiiis', $winners[0], $winners[1], $winners[2], $now, $draw_id);
    $stmt->execute();
    $stmt->close();
    $drawn++;
}

$elapsed = round(microtime(true) - $start, 4);
$stmt = db()->prepare("INSERT INTO cron_logs (cron_name, status, message, records_affected, execution_time) VALUES ('lucky_draw_processor', 'success', ?, ?, ?)");
$msg = "Draws processed: {$drawn}";
$stmt->bind_param('sid', $msg, $drawn, $elapsed);
$stmt->execute();
$stmt->close();

echo "[" . date('Y-m-d H:i:s') . "] lucky_draw: {$drawn} draws ({$elapsed}s)\n";

<?php
/**
 * NOXARA - Mining 2-Hour Logic
 */

require_once INCLUDES_PATH . '/wallet.php';
require_once INCLUDES_PATH . '/notification.php';

function start_mining(int $user_id, int $user_product_id): array {
    $stmt = db()->prepare("SELECT * FROM user_products WHERE id = ? AND user_id = ? AND status = 'active'");
    $stmt->bind_param('ii', $user_product_id, $user_id);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$pkg) return ['success' => false, 'message' => 'Paket tidak ditemukan atau tidak aktif.'];
    
    $today = date('Y-m-d');
    
    // Check if already mined today
    if ($pkg['last_mining_date'] === $today) {
        return ['success' => false, 'message' => 'Anda sudah mining hari ini untuk paket ini. Hadir lagi besok 00:01.'];
    }
    
    // Check if currently mining
    if ($pkg['mining_state'] === 'mining') {
        return ['success' => false, 'message' => 'Mining sedang berjalan.'];
    }
    
    $now = date('Y-m-d H:i:s');
    $finish = date('Y-m-d H:i:s', strtotime('+' . MINING_DURATION_HOURS . ' hours'));
    
    db()->begin_transaction();
    try {
        // Update product mining state
        $stmt = db()->prepare("UPDATE user_products SET mining_state = 'mining', mining_started_at = ?, mining_finished_at = ?, last_mining_date = ? WHERE id = ?");
        $stmt->bind_param('sssi', $now, $finish, $today, $user_product_id);
        $stmt->execute();
        $stmt->close();
        
        // Insert mining log
        $stmt = db()->prepare("INSERT INTO mining_logs (user_id, user_product_id, profit_amount, status, started_at, mining_date) VALUES (?, ?, ?, 'mining', ?, ?)");
        $stmt->bind_param('iidss', $user_id, $user_product_id, $pkg['daily_profit'], $now, $today);
        $stmt->execute();
        $stmt->close();
        
        db()->commit();
        return [
            'success' => true,
            'message' => 'Mining dimulai! Profit akan cair dalam 2 jam.',
            'finish_at' => $finish,
            'profit' => $pkg['daily_profit']
        ];
    } catch (Exception $e) {
        db()->rollback();
        return ['success' => false, 'message' => 'Gagal memulai mining.'];
    }
}

function finalize_mining(int $mining_log_id): bool {
    $stmt = db()->prepare("SELECT ml.*, up.daily_profit, up.days_claimed, up.user_id FROM mining_logs ml JOIN user_products up ON up.id = ml.user_product_id WHERE ml.id = ? AND ml.status = 'mining'");
    $stmt->bind_param('i', $mining_log_id);
    $stmt->execute();
    $log = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$log) return false;
    
    $now = date('Y-m-d H:i:s');
    $profit = (float)$log['profit_amount'];
    $user_id = $log['user_id'];
    
    db()->begin_transaction();
    try {
        // Credit profit to balance
        credit_balance($user_id, $profit, 'Profit mining ' . date('d/m/Y'), 'mining', $mining_log_id);
        
        // Update mining log
        $stmt = db()->prepare("UPDATE mining_logs SET status = 'completed', finished_at = ? WHERE id = ?");
        $stmt->bind_param('si', $now, $mining_log_id);
        $stmt->execute();
        $stmt->close();
        
        // Update product: days_claimed++, total_earned++, mining_state done
        $stmt = db()->prepare("UPDATE user_products SET days_claimed = days_claimed + 1, total_earned = total_earned + ?, mining_state = 'done' WHERE id = ?");
        $stmt->bind_param('di', $profit, $log['user_product_id']);
        $stmt->execute();
        $stmt->close();
        
        // Record transaction
        record_transaction($user_id, 'mining_profit', $profit, 0, 'Profit mining harian', (string)$mining_log_id);
        
        // Update mining streak
        $stmt = db()->prepare("UPDATE users SET mining_streak = mining_streak + 1 WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Notification
        notify_user($user_id, 'Profit Mining Cair', 'Profit ' . format_rupiah($profit) . ' telah masuk ke saldo Anda.', 'mining');
        
        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollback();
        error_log('Mining finalize error: ' . $e->getMessage());
        return false;
    }
}

function claim_mining_manual(int $user_id, int $user_product_id): array {
    $stmt = db()->prepare("SELECT id, mining_finished_at FROM mining_logs WHERE user_id = ? AND user_product_id = ? AND status = 'mining' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('ii', $user_id, $user_product_id);
    $stmt->execute();
    $log = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$log) return ['success' => false, 'message' => 'Tidak ada mining aktif untuk diklaim.'];
    
    // Check if 2 hours passed
    $finish_time = strtotime($log['mining_finished_at'] ?? '+2 hours');
    if (time() < $finish_time) {
        $remaining = $finish_time - time();
        $mins = ceil($remaining / 60);
        return ['success' => false, 'message' => "Mining belum selesai. Tunggu {$mins} menit lagi."];
    }
    
    $result = finalize_mining($log['id']);
    if ($result) {
        return ['success' => true, 'message' => 'Profit berhasil diklaim!'];
    }
    return ['success' => false, 'message' => 'Gagal mengklaim profit.'];
}

<?php
/**
 * NOXARA - Daily Bonus System
 */

require_once INCLUDES_PATH . '/wallet.php';
require_once INCLUDES_PATH . '/notification.php';

function claim_checkin(int $user_id): array {
    $today = date('Y-m-d');
    
    // Check if already claimed today
    $stmt = db()->prepare("SELECT id FROM daily_checkins WHERE user_id = ? AND checkin_date = ?");
    $stmt->bind_param('is', $user_id, $today);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Anda sudah klaim check-in hari ini.'];
    }
    $stmt->close();
    
    // Get user streak
    $stmt = db()->prepare("SELECT current_streak, last_checkin_date FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $streak = (int)$user['current_streak'];
    $last = $user['last_checkin_date'];
    
    // Check if streak continues (yesterday) or resets
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if ($last === $yesterday) {
        $streak++;
    } else {
        $streak = 1; // Reset
    }
    
    // Day number (1-7, cycles)
    $day_num = (($streak - 1) % 7) + 1;
    
    // Get reward for this day
    $stmt = db()->prepare("SELECT reward_amount FROM daily_checkin_settings WHERE day_number = ?");
    $stmt->bind_param('i', $day_num);
    $stmt->execute();
    $setting = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $reward = $setting ? (float)$setting['reward_amount'] : 100;
    
    db()->begin_transaction();
    try {
        // Record check-in
        $stmt = db()->prepare("INSERT INTO daily_checkins (user_id, day_number, reward_amount, checkin_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iids', $user_id, $day_num, $reward, $today);
        $stmt->execute();
        $stmt->close();
        
        // Update user streak
        $stmt = db()->prepare("UPDATE users SET current_streak = ?, last_checkin_date = ? WHERE id = ?");
        $stmt->bind_param('isi', $streak, $today, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Credit reward
        credit_balance($user_id, $reward, "Check-in Hari {$day_num}", 'checkin', $user_id);
        record_transaction($user_id, 'bonus', $reward, 0, "Daily check-in hari {$day_num}");
        
        db()->commit();
        return ['success' => true, 'message' => "Check-in berhasil! +{$reward}", 'day' => $day_num, 'reward' => $reward, 'streak' => $streak];
    } catch (Exception $e) {
        db()->rollback();
        return ['success' => false, 'message' => 'Gagal klaim check-in.'];
    }
}

function do_spin(int $user_id, bool $is_paid = false): array {
    $today = date('Y-m-d');
    
    if (!$is_paid) {
        // Check free spin count
        $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM spin_logs WHERE user_id = ? AND spin_date = ? AND is_paid_spin = 0");
        $stmt->bind_param('is', $user_id, $today);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();
        
        $settings = db()->query("SELECT free_spins_per_day FROM spin_settings LIMIT 1")->fetch_assoc();
        $max_free = $settings ? (int)$settings['free_spins_per_day'] : 1;
        
        if ($cnt >= $max_free) {
            return ['success' => false, 'message' => 'Spin gratis hari ini sudah habis.'];
        }
    } else {
        // Deduct spin price
        $settings = db()->query("SELECT extra_spin_price FROM spin_settings LIMIT 1")->fetch_assoc();
        $price = $settings ? (float)$settings['extra_spin_price'] : 5000;
        $w = get_wallet($user_id);
        if (((float)$w['balance'] + (float)$w['bonus_balance']) < $price) {
            return ['success' => false, 'message' => 'Saldo tidak cukup untuk beli spin.'];
        }
        spend_for_purchase($user_id, $price, 'Beli ekstra spin', 'spin', 0);
    }
    
    // Pick prize by probability
    $prizes = db()->query("SELECT * FROM spin_prizes ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
    $rand = mt_rand(1, 10000) / 10000;
    $cumulative = 0;
    $won = $prizes[0]; // fallback
    
    foreach ($prizes as $p) {
        $cumulative += (float)$p['probability'];
        if ($rand <= $cumulative) {
            $won = $p;
            break;
        }
    }
    
    $amount = (float)$won['amount'];
    $paid_flag = $is_paid ? 1 : 0;
    
    // Record spin
    $stmt = db()->prepare("INSERT INTO spin_logs (user_id, prize_id, amount, is_paid_spin, spin_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iidis', $user_id, $won['id'], $amount, $paid_flag, $today);
    $stmt->execute();
    $stmt->close();
    
    // Credit reward
    credit_balance($user_id, $amount, 'Hadiah spin wheel', 'spin', $won['id']);
    record_transaction($user_id, 'bonus', $amount, 0, 'Spin wheel: ' . $won['label']);
    
    return ['success' => true, 'prize' => $won, 'amount' => $amount, 'message' => 'Selamat! Anda mendapat ' . format_rupiah($amount)];
}

function open_mystery_box(int $user_id, string $box_type = 'free'): array {
    $today = date('Y-m-d');
    
    if ($box_type === 'free') {
        $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM mystery_box_logs WHERE user_id = ? AND box_type = 'free' AND open_date = ?");
        $stmt->bind_param('is', $user_id, $today);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();
        if ($cnt >= 1) return ['success' => false, 'message' => 'Mystery box gratis hari ini sudah dibuka.'];
    } else {
        $settings = db()->query("SELECT price FROM mystery_box_settings WHERE type = '{$box_type}'")->fetch_assoc();
        $price = $settings ? (float)$settings['price'] : 5000;
        $result = spend_for_purchase($user_id, $price, "Beli mystery box {$box_type}", 'mystery_box', 0);
        if (!$result['success']) return $result;
    }
    
    // Pick prize
    $stmt = db()->prepare("SELECT * FROM mystery_box_prizes WHERE box_type = ?");
    $stmt->bind_param('s', $box_type);
    $stmt->execute();
    $prizes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $rand = mt_rand(1, 10000) / 10000;
    $cumulative = 0;
    $won = $prizes[0];
    foreach ($prizes as $p) {
        $cumulative += (float)$p['probability'];
        if ($rand <= $cumulative) { $won = $p; break; }
    }
    
    $amount = (float)$won['amount'];
    
    $stmt = db()->prepare("INSERT INTO mystery_box_logs (user_id, box_type, amount, open_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isds', $user_id, $box_type, $amount, $today);
    $stmt->execute();
    $stmt->close();
    
    credit_balance($user_id, $amount, 'Hadiah mystery box', 'mystery_box', 0);
    record_transaction($user_id, 'bonus', $amount, 0, "Mystery box: " . format_rupiah($amount));
    
    return ['success' => true, 'amount' => $amount, 'message' => 'Selamat! Mystery box berisi ' . format_rupiah($amount)];
}

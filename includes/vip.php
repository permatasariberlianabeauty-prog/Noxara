<?php
/**
 * NOXARA - VIP System
 */

function vip_recalculate(int $user_id): void {
    $stmt = db()->prepare("SELECT total_topup, vip_level FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) return;
    
    $total = (float)$user['total_topup'];
    $current = (int)$user['vip_level'];
    
    // Get all VIP levels sorted
    $levels = db()->query("SELECT * FROM vip_levels ORDER BY level DESC")->fetch_all(MYSQLI_ASSOC);
    
    $new_level = 0;
    foreach ($levels as $lv) {
        if ($total >= (float)$lv['min_topup']) {
            $new_level = (int)$lv['level'];
            break;
        }
    }
    
    // VIP can only go UP
    if ($new_level > $current) {
        $stmt = db()->prepare("UPDATE users SET vip_level = ? WHERE id = ?");
        $stmt->bind_param('ii', $new_level, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Notification
        require_once INCLUDES_PATH . '/notification.php';
        notify_user($user_id, 'VIP Upgrade!', "Selamat! Anda naik ke VIP {$new_level}.", 'vip');
        
        // Activity feed
        require_once INCLUDES_PATH . '/activity_feed.php';
        $stmt = db()->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        add_activity($user_id, mask_username($u['username']) . " naik VIP {$new_level}!", 'vip');
        
        // Achievement check
        require_once INCLUDES_PATH . '/achievement.php';
        check_achievement($user_id, 'vip_' . $new_level);
    }
}

function get_vip_info(int $level): ?array {
    $stmt = db()->prepare("SELECT * FROM vip_levels WHERE level = ?");
    $stmt->bind_param('i', $level);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function redeem_vip_code(int $user_id, string $code): array {
    $stmt = db()->prepare("SELECT * FROM vip_codes WHERE code = ? AND status = 'active'");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $vc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$vc) return ['success' => false, 'message' => 'Kode VIP tidak valid atau sudah tidak aktif.'];
    if ($vc['expires_at'] && strtotime($vc['expires_at']) < time()) {
        return ['success' => false, 'message' => 'Kode VIP sudah expired.'];
    }
    if ($vc['max_uses'] > 0 && $vc['used_count'] >= $vc['max_uses']) {
        return ['success' => false, 'message' => 'Kode VIP sudah habis dipakai.'];
    }
    
    $target_level = (int)$vc['target_vip_level'];
    $stmt = db()->prepare("SELECT vip_level FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ((int)$user['vip_level'] >= $target_level) {
        return ['success' => false, 'message' => 'VIP Anda sudah level ' . $user['vip_level'] . ' atau lebih tinggi.'];
    }
    
    // Apply
    $stmt = db()->prepare("UPDATE users SET vip_level = ? WHERE id = ?");
    $stmt->bind_param('ii', $target_level, $user_id);
    $stmt->execute();
    $stmt->close();
    
    $stmt = db()->prepare("UPDATE vip_codes SET used_count = used_count + 1 WHERE id = ?");
    $stmt->bind_param('i', $vc['id']);
    $stmt->execute();
    $stmt->close();
    
    if ($vc['max_uses'] > 0 && ($vc['used_count'] + 1) >= $vc['max_uses']) {
        $stmt = db()->prepare("UPDATE vip_codes SET status = 'used' WHERE id = ?");
        $stmt->bind_param('i', $vc['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    return ['success' => true, 'message' => "Berhasil! VIP Anda sekarang level {$target_level}."];
}

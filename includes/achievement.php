<?php
/**
 * NOXARA - Achievement System
 */

function check_achievement(int $user_id, string $key): void {
    $stmt = db()->prepare("SELECT id, reward_amount, title FROM achievements WHERE achievement_key = ? AND is_active = 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $ach = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$ach) return;
    
    // Check if already unlocked
    $stmt = db()->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
    $stmt->bind_param('ii', $user_id, $ach['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) { $stmt->close(); return; }
    $stmt->close();
    
    // Unlock
    $stmt = db()->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $ach['id']);
    $stmt->execute();
    $stmt->close();
    
    // Auto-claim reward
    $reward = (float)$ach['reward_amount'];
    if ($reward > 0) {
        require_once INCLUDES_PATH . '/wallet.php';
        credit_balance($user_id, $reward, 'Achievement: ' . $ach['title'], 'achievement', $ach['id']);
        record_transaction($user_id, 'bonus', $reward, 0, 'Achievement unlock: ' . $ach['title']);
    }
    
    require_once INCLUDES_PATH . '/notification.php';
    notify_user($user_id, 'Achievement Unlocked!', $ach['title'] . ' - Reward ' . format_rupiah($reward), 'bonus');
}

function check_achievements_batch(int $user_id): void {
    $stmt = db()->prepare("SELECT vip_level, current_streak, mining_streak FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) return;
    
    // VIP achievements
    if ($user['vip_level'] >= 1) check_achievement($user_id, 'vip_1');
    if ($user['vip_level'] >= 2) check_achievement($user_id, 'vip_2');
    if ($user['vip_level'] >= 3) check_achievement($user_id, 'vip_3');
    
    // Streak
    if ($user['current_streak'] >= 7) check_achievement($user_id, 'streak_7');
    if ($user['current_streak'] >= 30) check_achievement($user_id, 'streak_30');
    if ($user['mining_streak'] >= 7) check_achievement($user_id, 'mining_streak_7');
    
    // Mining count
    $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM mining_logs WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $mc = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();
    if ($mc >= 1) check_achievement($user_id, 'first_mining');
    if ($mc >= 30) check_achievement($user_id, 'mining_30');
    
    // Referral count
    $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM referrals WHERE user_id = ? AND level = 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rc = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();
    if ($rc >= 1) check_achievement($user_id, 'refer_1');
    if ($rc >= 10) check_achievement($user_id, 'refer_10');
}

function get_user_achievements(int $user_id): array {
    $stmt = db()->prepare("SELECT a.*, ua.unlocked_at, ua.is_claimed FROM achievements a LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ? WHERE a.is_active = 1 ORDER BY a.sort_order");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

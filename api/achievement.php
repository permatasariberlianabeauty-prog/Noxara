<?php
/**
 * NOXARA - Achievement API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/achievement.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'list':
        $achievements = get_user_achievements($user['id']);
        json_response(['success' => true, 'data' => $achievements]);
        break;

    case 'claim':
        $achievement_id = (int)($input['achievement_id'] ?? 0);
        $stmt = db()->prepare("SELECT ua.*, a.reward_amount, a.title FROM user_achievements ua JOIN achievements a ON a.id = ua.achievement_id WHERE ua.user_id = ? AND ua.achievement_id = ? AND ua.is_claimed = 0");
        $stmt->bind_param('ii', $user['id'], $achievement_id);
        $stmt->execute();
        $ua = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$ua) json_response(['success' => false, 'message' => 'Achievement tidak tersedia untuk diklaim.']);

        $reward = (float)$ua['reward_amount'];
        if ($reward > 0) {
            require_once INCLUDES_PATH . '/wallet.php';
            credit_balance($user['id'], $reward, 'Achievement: ' . $ua['title'], 'achievement', $achievement_id);
            record_transaction($user['id'], 'bonus', $reward, 0, 'Achievement: ' . $ua['title']);
        }

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare("UPDATE user_achievements SET is_claimed = 1, claimed_at = ? WHERE id = ?");
        $stmt->bind_param('si', $now, $ua['id']);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'reward' => $reward, 'message' => 'Reward diklaim! +' . format_rupiah($reward)]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

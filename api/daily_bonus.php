<?php
/**
 * NOXARA - Daily Bonus API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/daily_bonus.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'claim_checkin':
        $result = claim_checkin($user['id']);
        json_response($result);
        break;

    case 'spin':
        $is_paid = (bool)($input['paid'] ?? false);
        $result = do_spin($user['id'], $is_paid);
        json_response($result);
        break;

    case 'claim_quest':
        $quest_id = (int)($input['quest_id'] ?? 0);
        $today = date('Y-m-d');
        $stmt = db()->prepare("SELECT uqp.*, qd.reward_amount, qd.title FROM user_quest_progress uqp JOIN quest_definitions qd ON qd.id = uqp.quest_id WHERE uqp.user_id = ? AND uqp.quest_id = ? AND uqp.quest_date = ? AND uqp.is_completed = 1 AND uqp.is_claimed = 0");
        $stmt->bind_param('iis', $user['id'], $quest_id, $today);
        $stmt->execute();
        $quest = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$quest) json_response(['success' => false, 'message' => 'Quest belum selesai atau sudah diklaim.']);

        $reward = (float)$quest['reward_amount'];
        credit_balance($user['id'], $reward, 'Quest: ' . $quest['title'], 'quest', $quest_id);
        record_transaction($user['id'], 'bonus', $reward, 0, 'Quest reward: ' . $quest['title']);

        $stmt = db()->prepare("UPDATE user_quest_progress SET is_claimed = 1 WHERE id = ?");
        $stmt->bind_param('i', $quest['id']);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'amount' => $reward, 'message' => 'Quest reward diklaim! +' . format_rupiah($reward)]);
        break;

    case 'mystery_box':
        $box_type = $input['type'] ?? 'free';
        if (!in_array($box_type, ['free', 'paid_basic', 'paid_premium'])) {
            json_response(['success' => false, 'message' => 'Tipe box tidak valid.']);
        }
        $result = open_mystery_box($user['id'], $box_type);
        json_response($result);
        break;

    case 'coin_hunt_start':
        // Return game config
        $settings = db()->query("SELECT * FROM coin_hunt_settings LIMIT 1")->fetch_assoc();
        $today = date('Y-m-d');
        $stmt = db()->prepare("SELECT SUM(total_reward) as earned FROM coin_hunt_logs WHERE user_id = ? AND play_date = ?");
        $stmt->bind_param('is', $user['id'], $today);
        $stmt->execute();
        $earned = (float)($stmt->get_result()->fetch_assoc()['earned'] ?? 0);
        $stmt->close();

        $max = (float)($settings['daily_max_reward'] ?? 2000);
        if ($earned >= $max) json_response(['success' => false, 'message' => 'Limit harian tercapai.']);

        json_response([
            'success' => true,
            'duration' => (int)($settings['duration_seconds'] ?? 30),
            'coin_min' => (float)($settings['coin_min_value'] ?? 50),
            'coin_max' => (float)($settings['coin_max_value'] ?? 100),
            'remaining' => $max - $earned
        ]);
        break;

    case 'coin_hunt_finish':
        $coins = (int)($input['coins_collected'] ?? 0);
        $settings = db()->query("SELECT * FROM coin_hunt_settings LIMIT 1")->fetch_assoc();
        $today = date('Y-m-d');

        // Server-side validation
        $max_coins = (int)($settings['duration_seconds'] ?? 30); // Max 1 coin per second
        $coins = min($coins, $max_coins);
        $per_coin = random_int((int)($settings['coin_min_value'] ?? 50), (int)($settings['coin_max_value'] ?? 100));
        $reward = $coins * $per_coin;

        // Check daily limit
        $stmt = db()->prepare("SELECT SUM(total_reward) as earned FROM coin_hunt_logs WHERE user_id = ? AND play_date = ?");
        $stmt->bind_param('is', $user['id'], $today);
        $stmt->execute();
        $earned = (float)($stmt->get_result()->fetch_assoc()['earned'] ?? 0);
        $stmt->close();

        $max = (float)($settings['daily_max_reward'] ?? 2000);
        $reward = min($reward, $max - $earned);
        if ($reward <= 0) json_response(['success' => false, 'message' => 'Limit harian tercapai.']);

        $stmt = db()->prepare("INSERT INTO coin_hunt_logs (user_id, coins_collected, total_reward, play_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iids', $user['id'], $coins, $reward, $today);
        $stmt->execute();
        $stmt->close();

        credit_balance($user['id'], $reward, 'Coin Hunt reward', 'coin_hunt', 0);
        record_transaction($user['id'], 'bonus', $reward, 0, 'Coin Hunt: ' . $coins . ' coins');

        json_response(['success' => true, 'reward' => $reward, 'coins' => $coins, 'message' => 'Selamat! +' . format_rupiah($reward)]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

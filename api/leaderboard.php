<?php
/**
 * NOXARA - Leaderboard API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/leaderboard.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $type = $_GET['type'] ?? 'miner_daily';
        $allowed = ['miner_daily', 'miner_weekly', 'miner_monthly', 'referrer_weekly', 'referrer_monthly', 'earner_alltime'];
        if (!in_array($type, $allowed)) json_response(['success' => false, 'message' => 'Type tidak valid.']);

        $data = get_leaderboard($type, 10);
        json_response(['success' => true, 'type' => $type, 'data' => $data]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

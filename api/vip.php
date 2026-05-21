<?php
/**
 * NOXARA - VIP API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/vip.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'redeem_code':
        $code = strtoupper(trim($input['code'] ?? ''));
        if (empty($code)) json_response(['success' => false, 'message' => 'Masukkan kode VIP.']);
        $result = redeem_vip_code($user['id'], $code);
        json_response($result);
        break;

    case 'progress':
        $stmt = db()->prepare("SELECT total_topup, vip_level FROM users WHERE id = ?");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $levels = db()->query("SELECT * FROM vip_levels ORDER BY level")->fetch_all(MYSQLI_ASSOC);
        $current = (int)$u['vip_level'];
        $next_level = null;
        foreach ($levels as $lv) {
            if ((int)$lv['level'] > $current) { $next_level = $lv; break; }
        }

        $progress = 100;
        if ($next_level) {
            $needed = (float)$next_level['min_topup'];
            $progress = $needed > 0 ? min(100, round(((float)$u['total_topup'] / $needed) * 100)) : 100;
        }

        json_response([
            'success' => true,
            'current_level' => $current,
            'total_topup' => (float)$u['total_topup'],
            'next_level' => $next_level,
            'progress' => $progress
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

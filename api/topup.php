<?php
/**
 * NOXARA - Topup API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/topup.php';
require_once INCLUDES_PATH . '/voucher.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'validate_voucher':
        $code = trim($input['code'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        if (empty($code)) json_response(['valid' => false, 'message' => 'Masukkan kode voucher.']);
        $result = validate_voucher($code, $user['id'], 'topup_bonus', $amount);
        if ($result['valid']) {
            json_response(['valid' => true, 'bonus' => $result['bonus'], 'voucher_id' => $result['voucher']['id']]);
        }
        json_response($result);
        break;

    case 'create':
        $amount = (float)($input['amount'] ?? 0);
        $voucher_id = !empty($input['voucher_id']) ? (int)$input['voucher_id'] : null;
        $result = create_topup($user['id'], $amount, $voucher_id);
        json_response($result);
        break;

    case 'status':
        $topup_id = (int)($_GET['id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM topups WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $topup_id, $user['id']);
        $stmt->execute();
        $topup = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$topup) json_response(['success' => false, 'message' => 'Topup tidak ditemukan.']);

        if ($topup['status'] === 'pending' && $topup['transaction_id']) {
            // Check with Cashify
            $check = cashify_check_status($topup['transaction_id']);
            if (isset($check['data']['status']) && $check['data']['status'] === 'paid') {
                process_topup_paid($topup_id);
                json_response(['success' => true, 'status' => 'paid']);
            }
        }

        json_response(['success' => true, 'status' => $topup['status']]);
        break;

    case 'cancel':
        $topup_id = (int)($input['topup_id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM topups WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->bind_param('ii', $topup_id, $user['id']);
        $stmt->execute();
        $topup = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$topup) json_response(['success' => false, 'message' => 'Topup tidak ditemukan.']);

        // Cancel with Cashify
        if ($topup['transaction_id']) {
            cashify_cancel($topup['transaction_id']);
        }

        $stmt = db()->prepare("UPDATE topups SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $topup_id);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'message' => 'Pembayaran dibatalkan.']);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

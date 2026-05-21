<?php
/**
 * NOXARA - Mining API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/mining.php';
require_once INCLUDES_PATH . '/wallet.php';
require_once INCLUDES_PATH . '/voucher.php';
require_once INCLUDES_PATH . '/referral.php';
require_once INCLUDES_PATH . '/ledger.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'start_mining':
        $up_id = (int)($input['user_product_id'] ?? 0);
        $result = start_mining($user['id'], $up_id);
        json_response($result);
        break;

    case 'claim_one':
        $up_id = (int)($input['user_product_id'] ?? 0);
        $result = claim_mining_manual($user['id'], $up_id);
        json_response($result);
        break;

    case 'claim_all':
        $stmt = db()->prepare("SELECT ml.id FROM mining_logs ml JOIN user_products up ON up.id = ml.user_product_id WHERE ml.user_id = ? AND ml.status = 'mining' AND ml.started_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $claimed = 0;
        foreach ($logs as $log) {
            if (finalize_mining($log['id'])) $claimed++;
        }
        json_response(['success' => true, 'claimed' => $claimed, 'message' => "{$claimed} profit berhasil diklaim!"]);
        break;

    case 'purchase':
        $product_id = (int)($input['product_id'] ?? 0);
        $pin = $input['pin'] ?? '';
        $voucher_id = !empty($input['voucher_id']) ? (int)$input['voucher_id'] : null;

        // Verify PIN
        if (empty($user['pin'])) {
            json_response(['success' => false, 'message' => 'Anda belum mengatur PIN. Silakan atur PIN di halaman Keamanan.']);
        }
        if (!password_verify($pin, $user['pin'])) {
            json_response(['success' => false, 'message' => 'PIN salah.']);
        }

        // Get product
        $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$product) json_response(['success' => false, 'message' => 'Produk tidak ditemukan.']);

        // VIP check
        if ($product['min_vip'] > $user['vip_level']) {
            json_response(['success' => false, 'message' => 'Paket ini hanya untuk VIP ' . $product['min_vip'] . ' ke atas.']);
        }

        $price = (float)$product['price'];
        $discount = 0;

        // Voucher
        if ($voucher_id) {
            $v_result = validate_voucher('', $user['id'], 'product_discount', $price, $product_id);
            // Re-validate by ID
            $stmt2 = db()->prepare("SELECT code FROM vouchers WHERE id = ?");
            $stmt2->bind_param('i', $voucher_id);
            $stmt2->execute();
            $vc = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
            if ($vc) {
                $v_result = validate_voucher($vc['code'], $user['id'], 'product_discount', $price, $product_id);
                if ($v_result['valid']) $discount = $v_result['discount'];
            }
        }

        $final_price = $price - $discount;
        if ($final_price < 0) $final_price = 0;

        // Spend
        $spend = spend_for_purchase($user['id'], $final_price, 'Beli paket ' . $product['name'], 'purchase', $product_id);
        if (!$spend['success']) json_response($spend);

        // Create user_product
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', strtotime('+' . $product['duration_days'] . ' days'));
        $from_bal = $spend['from_balance'];
        $from_bon = $spend['from_bonus'];

        $stmt = db()->prepare("INSERT INTO user_products (user_id, product_id, paid_amount, paid_from_balance, paid_from_bonus, daily_profit, duration_days, status, started_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)");
        $stmt->bind_param('iiddddiss', $user['id'], $product_id, $final_price, $from_bal, $from_bon, $product['daily_profit'], $product['duration_days'], $now, $expires);
        $stmt->execute();
        $up_id = $stmt->insert_id;
        $stmt->close();

        // Record transaction
        record_transaction($user['id'], 'purchase', $final_price, 0, 'Beli ' . $product['name'], (string)$up_id);

        // Distribute referral commissions
        distribute_commissions($user['id'], $final_price, $up_id);

        // Mark voucher
        if ($voucher_id && $discount > 0) {
            mark_voucher_used($user['id'], $voucher_id);
        }

        // Achievement
        require_once INCLUDES_PATH . '/achievement.php';
        check_achievement($user['id'], 'first_purchase');

        // Notification
        require_once INCLUDES_PATH . '/notification.php';
        notify_user($user['id'], 'Pembelian Berhasil', 'Paket ' . $product['name'] . ' aktif selama ' . $product['duration_days'] . ' hari.', 'system');

        json_response(['success' => true, 'message' => 'Pembelian berhasil! Paket aktif.']);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

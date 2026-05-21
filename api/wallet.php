<?php
/**
 * NOXARA - Wallet API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/wallet.php';
require_once INCLUDES_PATH . '/voucher.php';
require_once INCLUDES_PATH . '/ledger.php';
require_once INCLUDES_PATH . '/notification.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'balance':
        $w = get_wallet($user['id']);
        json_response([
            'success' => true,
            'balance' => (float)$w['balance'],
            'bonus_balance' => (float)$w['bonus_balance'],
            'total' => (float)$w['balance'] + (float)$w['bonus_balance']
        ]);
        break;

    case 'validate_voucher':
        $code = trim($input['code'] ?? '');
        $type = $input['type'] ?? 'product_discount';
        $amount = (float)($input['amount'] ?? 0);
        $product_id = !empty($input['product_id']) ? (int)$input['product_id'] : null;
        if (empty($code)) json_response(['valid' => false, 'message' => 'Masukkan kode voucher.']);
        $result = validate_voucher($code, $user['id'], $type, $amount, $product_id);
        if ($result['valid']) {
            json_response(['valid' => true, 'discount' => $result['discount'], 'voucher_id' => $result['voucher']['id']]);
        }
        json_response($result);
        break;

    case 'redeem_voucher':
        $code = strtoupper(trim($input['code'] ?? ''));
        if (empty($code)) json_response(['success' => false, 'message' => 'Masukkan kode voucher.']);
        $result = validate_voucher($code, $user['id'], 'cash_redeem', 0);
        if (!$result['valid']) json_response(['success' => false, 'message' => $result['message']]);

        $voucher = $result['voucher'];
        $amount = $result['bonus'];
        $target = $voucher['wallet_target'] ?? 'balance';

        if ($target === 'balance') {
            credit_balance($user['id'], $amount, 'Redeem voucher ' . $code, 'voucher', $voucher['id']);
        } else {
            credit_bonus($user['id'], $amount, 'Redeem voucher ' . $code, 'voucher', $voucher['id']);
        }
        mark_voucher_used($user['id'], $voucher['id']);
        record_transaction($user['id'], 'bonus', $amount, 0, 'Voucher redeem: ' . $code);
        notify_user($user['id'], 'Voucher Berhasil', 'Anda mendapat ' . format_rupiah($amount) . ' dari voucher ' . $code, 'bonus');

        json_response(['success' => true, 'amount' => $amount, 'message' => 'Voucher berhasil diklaim! +' . format_rupiah($amount)]);
        break;

    case 'withdraw':
        $bank_id = (int)($input['bank_account_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);

        $w = get_wallet($user['id']);
        if ((float)$w['bonus_balance'] > 0) {
            json_response(['success' => false, 'message' => 'Withdraw diblokir. Gunakan saldo bonus terlebih dahulu.']);
        }

        // Get VIP rules
        $stmt = db()->prepare("SELECT * FROM vip_levels WHERE level = ?");
        $stmt->bind_param('i', $user['vip_level']);
        $stmt->execute();
        $vip = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $min_wd = (float)($vip['min_withdraw'] ?? 100000);
        $fee_pct = (float)($vip['withdraw_fee_percent'] ?? 10);

        if ($amount < $min_wd) json_response(['success' => false, 'message' => 'Minimal withdraw ' . format_rupiah($min_wd)]);
        if ($amount > (float)$w['balance']) json_response(['success' => false, 'message' => 'Saldo tidak cukup.']);

        // Verify bank
        $stmt = db()->prepare("SELECT * FROM bank_accounts WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $bank_id, $user['id']);
        $stmt->execute();
        $bank = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$bank) json_response(['success' => false, 'message' => 'Rekening tidak ditemukan.']);

        $fee = round($amount * $fee_pct / 100);
        $net = $amount - $fee;

        // Hold balance
        if (!debit_balance($user['id'], $amount, 'Withdraw pending #' . time(), 'withdraw', 0)) {
            json_response(['success' => false, 'message' => 'Gagal memproses.']);
        }

        $stmt = db()->prepare("INSERT INTO withdrawals (user_id, bank_account_id, amount, fee, net_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('iiddd', $user['id'], $bank_id, $amount, $fee, $net);
        $stmt->execute();
        $wd_id = $stmt->insert_id;
        $stmt->close();

        record_transaction($user['id'], 'withdraw', $amount, $fee, 'Pengajuan withdraw', (string)$wd_id, 'pending');
        notify_user($user['id'], 'Withdraw Diajukan', 'Penarikan ' . format_rupiah($net) . ' sedang diproses.', 'withdraw');

        json_response(['success' => true, 'message' => 'Pengajuan withdraw berhasil! Menunggu proses admin.']);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

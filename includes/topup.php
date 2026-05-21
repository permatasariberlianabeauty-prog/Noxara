<?php
/**
 * NOXARA - Topup QRIS (Cashify Integration)
 */

function cashify_generate_qris(float $amount): array {
    $payload = [
        'id' => CASHIFY_QRIS_ID,
        'amount' => (int)$amount,
        'useUniqueCode' => true,
        'packageIds' => json_decode(CASHIFY_PACKAGE_IDS, true),
        'expiredInMinutes' => CASHIFY_EXPIRED_MINUTES
    ];
    
    $response = cashify_request('POST', CASHIFY_BASE_URL . '/qris', $payload);
    return $response;
}

function cashify_check_status(string $transaction_id): array {
    $payload = ['transactionId' => $transaction_id];
    return cashify_request('POST', CASHIFY_BASE_URL . '/check-status', $payload);
}

function cashify_cancel(string $transaction_id): array {
    $payload = ['transactionId' => $transaction_id];
    return cashify_request('POST', CASHIFY_BASE_URL . '/cancel-status', $payload);
}

function cashify_request(string $method, string $url, array $data = []): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-license-key: ' . CASHIFY_API_KEY
        ]
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log('Cashify cURL error: ' . $error);
        return ['success' => false, 'message' => 'Gagal terhubung ke payment gateway.'];
    }
    
    $result = json_decode($response, true);
    if (!$result) {
        return ['success' => false, 'message' => 'Response tidak valid dari payment gateway.'];
    }
    
    return $result;
}

function create_topup(int $user_id, float $amount, ?int $voucher_id = null): array {
    if ($amount < MIN_TOPUP) return ['success' => false, 'message' => 'Minimal top up ' . format_rupiah(MIN_TOPUP)];
    if ($amount > MAX_TOPUP) return ['success' => false, 'message' => 'Maksimal top up ' . format_rupiah(MAX_TOPUP)];
    
    // Generate QRIS
    $qris = cashify_generate_qris($amount);
    if (!isset($qris['data'])) {
        return ['success' => false, 'message' => $qris['message'] ?? 'Gagal generate QRIS.'];
    }
    
    $data = $qris['data'];
    $total_amount = (float)$data['totalAmount'];
    $unique = (int)($data['uniqueNominal'] ?? 0);
    $tx_id = $data['transactionId'];
    $qr_string = $data['qr_string'] ?? '';
    $expires = date('Y-m-d H:i:s', strtotime('+' . CASHIFY_EXPIRED_MINUTES . ' minutes'));
    
    // Calculate voucher bonus
    $voucher_bonus = 0;
    if ($voucher_id) {
        require_once INCLUDES_PATH . '/voucher.php';
        $vb = calculate_voucher_bonus($voucher_id, $total_amount, $user_id);
        $voucher_bonus = $vb['bonus'] ?? 0;
    }
    
    $stmt = db()->prepare("INSERT INTO topups (user_id, amount, total_amount, unique_nominal, transaction_id, qr_string, voucher_id, voucher_bonus, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param('iddissibs', $user_id, $amount, $total_amount, $unique, $tx_id, $qr_string, $voucher_id, $voucher_bonus, $expires);
    $stmt->execute();
    $topup_id = $stmt->insert_id;
    $stmt->close();
    
    return [
        'success' => true,
        'topup_id' => $topup_id,
        'transaction_id' => $tx_id,
        'qr_string' => $qr_string,
        'total_amount' => $total_amount,
        'unique_nominal' => $unique,
        'expires_at' => $expires
    ];
}

function process_topup_paid(int $topup_id): bool {
    $stmt = db()->prepare("SELECT * FROM topups WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $topup_id);
    $stmt->execute();
    $topup = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$topup) return false;
    
    require_once INCLUDES_PATH . '/wallet.php';
    require_once INCLUDES_PATH . '/vip.php';
    require_once INCLUDES_PATH . '/notification.php';
    require_once INCLUDES_PATH . '/activity_feed.php';
    
    $user_id = $topup['user_id'];
    $total = (float)$topup['total_amount'];
    $bonus = (float)$topup['voucher_bonus'];
    
    db()->begin_transaction();
    try {
        // Update topup status
        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare("UPDATE topups SET status = 'paid', paid_at = ? WHERE id = ?");
        $stmt->bind_param('si', $now, $topup_id);
        $stmt->execute();
        $stmt->close();
        
        // Credit balance
        credit_balance($user_id, $total, 'Top up via QRIS', 'topup', $topup_id);
        
        // Credit voucher bonus if any
        if ($bonus > 0) {
            credit_balance($user_id, $bonus, 'Bonus voucher top up', 'topup_bonus', $topup_id);
            if ($topup['voucher_id']) {
                require_once INCLUDES_PATH . '/voucher.php';
                mark_voucher_used($user_id, $topup['voucher_id']);
            }
        }
        
        // Update total_topup
        $stmt = db()->prepare("UPDATE users SET total_topup = total_topup + ? WHERE id = ?");
        $stmt->bind_param('di', $total, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Record transaction
        record_transaction($user_id, 'topup', $total, 0, 'Top up QRIS', (string)$topup_id);
        
        // VIP recalculate
        vip_recalculate($user_id);
        
        // Notification
        notify_user($user_id, 'Top Up Berhasil', 'Saldo ' . format_rupiah($total) . ' telah ditambahkan.', 'topup');
        
        // Activity feed
        $stmt2 = db()->prepare("SELECT username FROM users WHERE id = ?");
        $stmt2->bind_param('i', $user_id);
        $stmt2->execute();
        $u = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        add_activity($user_id, mask_username($u['username']) . ' baru topup ' . format_rupiah($total), 'topup');
        
        db()->commit();
        return true;
    } catch (Exception $e) {
        db()->rollback();
        error_log('Process topup error: ' . $e->getMessage());
        return false;
    }
}

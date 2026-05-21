<?php
/**
 * NOXARA - Wallet Functions
 */

require_once INCLUDES_PATH . '/ledger.php';

function get_wallet(int $user_id): array {
    $stmt = db()->prepare("SELECT balance, bonus_balance, total_earned, total_withdrawn FROM user_wallets WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $w = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $w ?: ['balance' => 0, 'bonus_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0];
}

function get_total_balance(int $user_id): float {
    $w = get_wallet($user_id);
    return (float)$w['balance'] + (float)$w['bonus_balance'];
}

function credit_balance(int $user_id, float $amount, string $desc, string $ref_type = '', int $ref_id = 0): bool {
    if ($amount <= 0) return false;
    $w = get_wallet($user_id);
    $before = (float)$w['balance'];
    $after = $before + $amount;
    
    $stmt = db()->prepare("UPDATE user_wallets SET balance = balance + ?, total_earned = total_earned + ? WHERE user_id = ?");
    $stmt->bind_param('ddi', $amount, $amount, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        ledger_write($user_id, 'balance', 'credit', $amount, $before, $after, $desc, $ref_type, $ref_id);
    }
    return $result;
}

function credit_bonus(int $user_id, float $amount, string $desc, string $ref_type = '', int $ref_id = 0): bool {
    if ($amount <= 0) return false;
    $w = get_wallet($user_id);
    $before = (float)$w['bonus_balance'];
    $after = $before + $amount;
    
    $stmt = db()->prepare("UPDATE user_wallets SET bonus_balance = bonus_balance + ? WHERE user_id = ?");
    $stmt->bind_param('di', $amount, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        ledger_write($user_id, 'bonus_balance', 'credit', $amount, $before, $after, $desc, $ref_type, $ref_id);
    }
    return $result;
}

function debit_balance(int $user_id, float $amount, string $desc, string $ref_type = '', int $ref_id = 0): bool {
    if ($amount <= 0) return false;
    $w = get_wallet($user_id);
    $before = (float)$w['balance'];
    if ($before < $amount) return false;
    $after = $before - $amount;
    
    $stmt = db()->prepare("UPDATE user_wallets SET balance = balance - ? WHERE user_id = ? AND balance >= ?");
    $stmt->bind_param('did', $amount, $user_id, $amount);
    $stmt->execute();
    $result = $stmt->affected_rows > 0;
    $stmt->close();
    
    if ($result) {
        ledger_write($user_id, 'balance', 'debit', $amount, $before, $after, $desc, $ref_type, $ref_id);
    }
    return $result;
}

/**
 * Spend for purchase: deduct bonus_balance first, then balance
 */
function spend_for_purchase(int $user_id, float $total, string $desc, string $ref_type = '', int $ref_id = 0): array {
    $w = get_wallet($user_id);
    $balance = (float)$w['balance'];
    $bonus = (float)$w['bonus_balance'];
    $available = $balance + $bonus;
    
    if ($available < $total) {
        return ['success' => false, 'message' => 'Saldo tidak cukup. Butuh ' . format_rupiah($total) . ', tersedia ' . format_rupiah($available)];
    }
    
    $from_bonus = min($bonus, $total);
    $from_balance = $total - $from_bonus;
    
    db()->begin_transaction();
    try {
        if ($from_bonus > 0) {
            $before_b = $bonus;
            $after_b = $bonus - $from_bonus;
            $stmt = db()->prepare("UPDATE user_wallets SET bonus_balance = bonus_balance - ? WHERE user_id = ?");
            $stmt->bind_param('di', $from_bonus, $user_id);
            $stmt->execute();
            $stmt->close();
            ledger_write($user_id, 'bonus_balance', 'debit', $from_bonus, $before_b, $after_b, $desc, $ref_type, $ref_id);
        }
        
        if ($from_balance > 0) {
            $before_bal = $balance;
            $after_bal = $balance - $from_balance;
            $stmt = db()->prepare("UPDATE user_wallets SET balance = balance - ? WHERE user_id = ?");
            $stmt->bind_param('di', $from_balance, $user_id);
            $stmt->execute();
            $stmt->close();
            ledger_write($user_id, 'balance', 'debit', $from_balance, $before_bal, $after_bal, $desc, $ref_type, $ref_id);
        }
        
        db()->commit();
        return ['success' => true, 'from_balance' => $from_balance, 'from_bonus' => $from_bonus];
    } catch (Exception $e) {
        db()->rollback();
        return ['success' => false, 'message' => 'Gagal memproses pembayaran.'];
    }
}

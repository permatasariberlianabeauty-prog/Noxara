<?php
/**
 * NOXARA - Transaction Ledger Writer
 */

function ledger_write(int $user_id, string $wallet_type, string $type, float $amount, float $before, float $after, string $desc, string $ref_type = '', int $ref_id = 0): void {
    $stmt = db()->prepare("INSERT INTO transaction_ledger (user_id, wallet_type, type, amount, balance_before, balance_after, description, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issdddssi', $user_id, $wallet_type, $type, $amount, $before, $after, $desc, $ref_type, $ref_id);
    $stmt->execute();
    $stmt->close();
}

function record_transaction(int $user_id, string $type, float $amount, float $fee, string $desc, string $ref_id = '', string $status = 'completed'): int {
    $net = $amount - $fee;
    $stmt = db()->prepare("INSERT INTO transactions (user_id, type, amount, fee, net_amount, description, reference_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isdddsss', $user_id, $type, $amount, $fee, $net, $desc, $ref_id, $status);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}

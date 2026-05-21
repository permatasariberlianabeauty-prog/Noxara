<?php
/**
 * NOXARA - Voucher System (3 Types, VIP-Locked)
 */

function validate_voucher(string $code, int $user_id, string $type, float $purchase_amount = 0, ?int $product_id = null): array {
    $stmt = db()->prepare("SELECT * FROM vouchers WHERE code = ? AND type = ? AND status = 'active'");
    $stmt->bind_param('ss', $code, $type);
    $stmt->execute();
    $v = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$v) return ['valid' => false, 'message' => 'Kode voucher tidak ditemukan.'];
    if ($v['expires_at'] && strtotime($v['expires_at']) < time()) return ['valid' => false, 'message' => 'Voucher sudah expired.'];
    if ($v['max_uses'] > 0 && $v['used_count'] >= $v['max_uses']) return ['valid' => false, 'message' => 'Voucher sudah habis.'];
    
    // Check user usage
    $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM user_vouchers WHERE user_id = ? AND voucher_id = ?");
    $stmt->bind_param('ii', $user_id, $v['id']);
    $stmt->execute();
    $usage = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($usage['cnt'] >= $v['max_per_user']) return ['valid' => false, 'message' => 'Anda sudah menggunakan voucher ini.'];
    
    // VIP check
    $stmt = db()->prepare("SELECT vip_level FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $vip = (int)$user['vip_level'];
    
    if ($vip < (int)$v['min_vip'] || $vip > (int)$v['max_vip']) {
        $min_topup_needed = 0;
        $vl = db()->query("SELECT min_topup FROM vip_levels WHERE level = " . (int)$v['min_vip'])->fetch_assoc();
        if ($vl) $min_topup_needed = $vl['min_topup'];
        return ['valid' => false, 'message' => 'Voucher hanya untuk VIP ' . $v['min_vip'] . ' ke atas. Naikkan VIP Anda dengan total isi ulang minimal ' . format_rupiah($min_topup_needed) . '.'];
    }
    
    // Min purchase
    if ($v['min_purchase'] > 0 && $purchase_amount < (float)$v['min_purchase']) {
        return ['valid' => false, 'message' => 'Minimal pembelian ' . format_rupiah($v['min_purchase']) . ' untuk voucher ini.'];
    }
    
    // Product applicability
    if ($product_id && $v['applicable_products']) {
        $products = json_decode($v['applicable_products'], true);
        if (is_array($products) && !in_array($product_id, $products)) {
            return ['valid' => false, 'message' => 'Voucher tidak berlaku untuk produk ini.'];
        }
    }
    
    // Calculate discount/bonus
    $discount = 0;
    if ($v['value_type'] === 'percent') {
        $discount = $purchase_amount * ((float)$v['value'] / 100);
    } else {
        $discount = (float)$v['value'];
    }
    
    return ['valid' => true, 'voucher' => $v, 'discount' => $discount, 'bonus' => $discount];
}

function calculate_voucher_bonus(int $voucher_id, float $amount, int $user_id): array {
    $stmt = db()->prepare("SELECT * FROM vouchers WHERE id = ?");
    $stmt->bind_param('i', $voucher_id);
    $stmt->execute();
    $v = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$v) return ['bonus' => 0];
    
    $bonus = 0;
    if ($v['value_type'] === 'percent') {
        $bonus = $amount * ((float)$v['value'] / 100);
    } else {
        $bonus = (float)$v['value'];
    }
    return ['bonus' => $bonus, 'voucher' => $v];
}

function mark_voucher_used(int $user_id, int $voucher_id): void {
    $stmt = db()->prepare("INSERT INTO user_vouchers (user_id, voucher_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $voucher_id);
    $stmt->execute();
    $stmt->close();
    
    $stmt = db()->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
    $stmt->bind_param('i', $voucher_id);
    $stmt->execute();
    $stmt->close();
}

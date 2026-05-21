<?php
/**
 * NOXARA - Referral System (3 Level)
 */

function build_referral_chain(int $user_id, int $referred_by): void {
    // Level 1: direct referrer
    $stmt = db()->prepare("INSERT IGNORE INTO referrals (user_id, referred_id, level) VALUES (?, ?, 1)");
    $stmt->bind_param('ii', $referred_by, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Level 2: referrer's referrer
    $stmt = db()->prepare("SELECT referred_by FROM users WHERE id = ?");
    $stmt->bind_param('i', $referred_by);
    $stmt->execute();
    $l2 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($l2 && $l2['referred_by']) {
        $stmt = db()->prepare("INSERT IGNORE INTO referrals (user_id, referred_id, level) VALUES (?, ?, 2)");
        $stmt->bind_param('ii', $l2['referred_by'], $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Level 3
        $stmt = db()->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->bind_param('i', $l2['referred_by']);
        $stmt->execute();
        $l3 = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($l3 && $l3['referred_by']) {
            $stmt = db()->prepare("INSERT IGNORE INTO referrals (user_id, referred_id, level) VALUES (?, ?, 3)");
            $stmt->bind_param('ii', $l3['referred_by'], $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function distribute_commissions(int $buyer_id, float $purchase_amount, int $user_product_id): void {
    require_once INCLUDES_PATH . '/wallet.php';
    require_once INCLUDES_PATH . '/notification.php';
    
    $levels = [1 => REFERRAL_L1_PERCENT, 2 => REFERRAL_L2_PERCENT, 3 => REFERRAL_L3_PERCENT];
    
    // Get upline chain
    $stmt = db()->prepare("SELECT user_id, level FROM referrals WHERE referred_id = ? ORDER BY level ASC");
    $stmt->bind_param('i', $buyer_id);
    $stmt->execute();
    $refs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    foreach ($refs as $ref) {
        $level = (int)$ref['level'];
        $upline_id = (int)$ref['user_id'];
        $percent = $levels[$level] ?? 0;
        if ($percent <= 0) continue;
        
        $commission = $purchase_amount * ($percent / 100);
        if ($commission <= 0) continue;
        
        // Credit commission to upline balance
        credit_balance($upline_id, $commission, "Komisi L{$level} dari pembelian downline", 'commission', $user_product_id);
        
        // Record commission
        $stmt = db()->prepare("INSERT INTO commissions (user_id, from_user_id, level, purchase_amount, commission_amount, user_product_id, status) VALUES (?, ?, ?, ?, ?, ?, 'paid')");
        $stmt->bind_param('iiiddi', $upline_id, $buyer_id, $level, $purchase_amount, $commission, $user_product_id);
        $stmt->execute();
        $stmt->close();
        
        record_transaction($upline_id, 'referral_commission', $commission, 0, "Komisi referral L{$level}", (string)$user_product_id);
        notify_user($upline_id, 'Komisi Referral', 'Anda mendapat komisi ' . format_rupiah($commission) . " dari downline L{$level}.", 'referral');
    }
}

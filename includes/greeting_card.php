<?php
/**
 * NOXARA - Greeting Card + Saldo
 */
$total_saldo = (float)($user['balance'] ?? 0) + (float)($user['bonus_balance'] ?? 0);
?>
<section class="greeting-section">
    <div class="greeting-text">
        <span class="greeting-hi">Halo, <?= sanitize($user['username'] ?? 'User') ?></span>
        <span class="greeting-badge badge-vip-<?= $user['vip_level'] ?? 0 ?>">VIP <?= $user['vip_level'] ?? 0 ?></span>
        <p class="greeting-time"><?= time_greeting() ?>!</p>
    </div>
</section>

<section class="saldo-card liquid-wave">
    <div class="saldo-header">
        <span class="saldo-label">Total Saldo</span>
        <button id="toggle-saldo" class="btn-icon-sm" aria-label="Toggle saldo">
            <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
    </div>
    <div class="saldo-amount" id="saldo-display">
        <span class="saldo-rp counter-animate" data-target="<?= $total_saldo ?>"><?= format_rupiah($total_saldo) ?></span>
    </div>
    <div class="saldo-actions">
        <a href="<?= BASE_URL ?>/pages/withdraw.php" class="saldo-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 7l-5-5-5 5"/></svg>
            <span>Tarik</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/topup.php" class="saldo-btn primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Isi Ulang</span>
        </a>
    </div>
</section>

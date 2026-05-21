<!-- Sidebar Drawer -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside id="sidebar" class="sidebar glassmorphism">
    <div class="sidebar-header">
        <div class="sidebar-user">
            <div class="sidebar-avatar vip-frame-<?= $user['vip_level'] ?? 0 ?>">
                <img src="<?= $user['avatar'] ? BASE_URL . $user['avatar'] : BASE_URL . '/assets/img/illustrations/avatar-default.svg' ?>" alt="Avatar">
            </div>
            <div class="sidebar-info">
                <span class="sidebar-name"><?= sanitize($user['username'] ?? 'User') ?></span>
                <span class="sidebar-vip badge-vip-<?= $user['vip_level'] ?? 0 ?>">VIP <?= $user['vip_level'] ?? 0 ?></span>
            </div>
        </div>
        <button id="btn-close-sidebar" class="btn-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            <span>Beranda</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/mining.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>Mining Center</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/products.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a4 4 0 0 0-8 0v2"/></svg>
            <span>Produk</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/topup.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Isi Saldo</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/withdraw.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 7l-5-5-5 5"/></svg>
            <span>Tarik Saldo</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/daily_bonus.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/></svg>
            <span>Daily Bonus</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/referral.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Referral</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/history.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Riwayat</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/profile.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Profil</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/contact.php" class="sidebar-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Bantuan</span>
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="sidebar-link text-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            <span>Keluar</span>
        </a>
    </nav>
</aside>

<!-- Bottom Navigation -->
<nav class="bottom-nav glassmorphism">
    <a href="<?= BASE_URL ?>/pages/dashboard.php" class="nav-item <?= ($current_page ?? '') === 'home' ? 'active' : '' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        <span>Home</span>
    </a>
    <a href="<?= BASE_URL ?>/pages/products.php" class="nav-item <?= ($current_page ?? '') === 'products' ? 'active' : '' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a4 4 0 0 0-8 0v2"/></svg>
        <span>Produk</span>
    </a>
    <button class="nav-item nav-fab" id="btn-fab" aria-label="Menu Cepat">
        <div class="fab-circle pulse-glow">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
    </button>
    <a href="<?= BASE_URL ?>/pages/referral.php" class="nav-item <?= ($current_page ?? '') === 'referral' ? 'active' : '' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Referral</span>
    </a>
    <a href="<?= BASE_URL ?>/pages/profile.php" class="nav-item <?= ($current_page ?? '') === 'profile' ? 'active' : '' ?>">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Akun</span>
    </a>
</nav>

<!-- FAB Sheet -->
<div id="fab-sheet-overlay" class="fab-sheet-overlay"></div>
<div id="fab-sheet" class="fab-sheet glassmorphism">
    <div class="fab-sheet-handle"></div>
    <div class="fab-sheet-grid">
        <a href="<?= BASE_URL ?>/pages/products.php" class="fab-item">
            <div class="fab-item-icon cyan"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a4 4 0 0 0-8 0v2"/></svg></div>
            <span>Beli Paket</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/my_packages.php" class="fab-item">
            <div class="fab-item-icon purple"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
            <span>Paket Aktif</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/mining.php" class="fab-item">
            <div class="fab-item-icon gold"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
            <span>Klaim Profit</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/withdraw.php" class="fab-item">
            <div class="fab-item-icon cyan"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 7l-5-5-5 5"/></svg></div>
            <span>Withdraw</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/voucher.php" class="fab-item">
            <div class="fab-item-icon purple"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7"/></svg></div>
            <span>Voucher</span>
        </a>
        <a href="<?= BASE_URL ?>/pages/contact.php" class="fab-item">
            <div class="fab-item-icon gold"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <span>Kontak</span>
        </a>
    </div>
</div>

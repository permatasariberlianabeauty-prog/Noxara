<?php
/**
 * NOXARA - Profile Page
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Profil';
$show_back = true;
$current_page = 'profile';

require_once INCLUDES_PATH . '/wallet.php';
require_once INCLUDES_PATH . '/achievement.php';
$wallet = get_wallet($user['id']);
$achievements = get_user_achievements($user['id']);
$total_saldo = (float)$wallet['balance'] + (float)$wallet['bonus_balance'];

// Stats
$stmt = db()->prepare("SELECT SUM(commission_amount) as total_comm FROM commissions WHERE user_id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$comm = $stmt->get_result()->fetch_assoc();
$stmt->close();
$total_commission = (float)($comm['total_comm'] ?? 0);

include INCLUDES_PATH . '/header.php';
?>

<section class="profile-section">
    <!-- Member Card -->
    <div class="member-card glassmorphism vip-card-<?= $user['vip_level'] ?>">
        <div class="member-card-bg"></div>
        <div class="member-card-content">
            <div class="member-avatar vip-frame-<?= $user['vip_level'] ?>">
                <img src="<?= $user['avatar'] ? BASE_URL . $user['avatar'] : BASE_URL . '/assets/img/illustrations/avatar-default.svg' ?>" alt="Avatar">
            </div>
            <div class="member-info">
                <span class="member-name"><?= sanitize($user['username']) ?></span>
                <span class="member-vip badge-vip-<?= $user['vip_level'] ?>">VIP <?= $user['vip_level'] ?></span>
                <span class="member-since">Bergabung <?= date_id($user['created_at']) ?></span>
            </div>
        </div>
        <div class="member-card-footer">
            <span class="ref-code"><?= $user['referral_code'] ?></span>
        </div>
    </div>

    <!-- Stats -->
    <div class="profile-stats glassmorphism">
        <div class="stat-item">
            <span class="stat-value counter-animate" data-target="<?= $wallet['total_earned'] ?>"><?= format_rupiah($wallet['total_earned']) ?></span>
            <span class="stat-label">Total Profit</span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= format_rupiah($user['total_topup']) ?></span>
            <span class="stat-label">Total Topup</span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= format_rupiah($wallet['total_withdrawn']) ?></span>
            <span class="stat-label">Total WD</span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= format_rupiah($total_commission) ?></span>
            <span class="stat-label">Komisi Referral</span>
        </div>
    </div>

    <!-- Achievement Badges -->
    <div class="achievement-wall glassmorphism">
        <h3 class="section-title-sm">Achievement Badges</h3>
        <div class="badges-grid">
            <?php foreach (array_slice($achievements, 0, 12) as $ach): ?>
            <div class="badge-item <?= $ach['unlocked_at'] ? 'unlocked' : 'locked' ?>">
                <div class="badge-icon"><?= $ach['unlocked_at'] ? '&#9733;' : '&#9734;' ?></div>
                <span class="badge-name"><?= sanitize($ach['title']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Menu List -->
    <div class="profile-menu glassmorphism">
        <a href="<?= BASE_URL ?>/pages/bank_account.php" class="profile-menu-item">
            <span>Rekening Bank</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/security.php" class="profile-menu-item">
            <span>PIN & Keamanan</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/history.php" class="profile-menu-item">
            <span>Riwayat Transaksi</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/download_app.php" class="profile-menu-item">
            <span>Download Aplikasi</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/info.php?slug=privacy" class="profile-menu-item">
            <span>Kebijakan Privasi</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/contact.php" class="profile-menu-item">
            <span>Bantuan & Kontak</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="profile-menu-item text-danger">
            <span>Keluar</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

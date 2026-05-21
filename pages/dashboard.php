<?php
/**
 * NOXARA - Dashboard (Home)
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$current_page = 'home';
$page_title = 'Beranda';

require_once INCLUDES_PATH . '/notification.php';
require_once INCLUDES_PATH . '/activity_feed.php';

// Get active mining status
$mining_active = db()->prepare("SELECT up.*, p.name as product_name FROM user_products up JOIN products p ON p.id = up.product_id WHERE up.user_id = ? AND up.status = 'active' AND up.mining_state = 'mining' LIMIT 1");
$mining_active->bind_param('i', $user['id']);
$mining_active->execute();
$active_mining = $mining_active->get_result()->fetch_assoc();
$mining_active->close();

// Marquee
$marquee = db()->query("SELECT content FROM marquee_settings WHERE is_active = 1 LIMIT 1")->fetch_assoc();

include INCLUDES_PATH . '/header.php';
?>

<!-- Marquee Ticker -->
<?php if ($marquee): ?>
<div class="marquee-bar">
    <div class="marquee-track">
        <span class="marquee-text"><?= sanitize($marquee['content']) ?></span>
        <span class="marquee-text"><?= sanitize($marquee['content']) ?></span>
    </div>
</div>
<?php endif; ?>

<!-- Greeting + Saldo -->
<?php include INCLUDES_PATH . '/greeting_card.php'; ?>

<!-- Active Mining Status Bar -->
<?php if ($active_mining): ?>
<section class="mining-status-bar glassmorphism">
    <div class="mining-status-icon pulse-glow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    <div class="mining-status-info">
        <span class="mining-product"><?= sanitize($active_mining['product_name']) ?></span>
        <span class="mining-countdown" data-finish="<?= $active_mining['mining_finished_at'] ?>">Menghitung...</span>
    </div>
    <a href="<?= BASE_URL ?>/pages/mining.php" class="btn btn-sm btn-outline">Lihat</a>
</section>
<?php endif; ?>

<!-- 8 Menu Grid -->
<?php include INCLUDES_PATH . '/home_menu_grid.php'; ?>

<!-- Banner Slider -->
<?php include INCLUDES_PATH . '/banner_slider.php'; ?>

<!-- Stats Live Counter -->
<?php include INCLUDES_PATH . '/stats_live.php'; ?>

<!-- Activity Feed -->
<?php include INCLUDES_PATH . '/activity_feed_render.php'; ?>

<!-- Trust Badges -->
<?php include INCLUDES_PATH . '/trust_badges.php'; ?>

<?php
$extra_js = '<script src="' . BASE_URL . '/assets/js/countdown.js"></script>';
include INCLUDES_PATH . '/footer.php';
?>

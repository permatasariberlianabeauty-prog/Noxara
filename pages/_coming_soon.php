<?php
/**
 * NOXARA - Coming Soon Placeholder
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Coming Soon';
$show_back = true;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section coming-soon-section">
    <div class="coming-soon-container glassmorphism">
        <div class="coming-soon-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#A78BFA" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <h2 class="gradient-text">Coming Soon</h2>
        <p>Fitur ini sedang dalam pengembangan dan akan segera hadir.</p>
        <p class="text-muted">Stay tuned untuk update terbaru!</p>
        <a href="<?= BASE_URL ?>/pages/dashboard.php" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

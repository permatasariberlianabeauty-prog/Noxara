<?php
/**
 * NOXARA - Download App
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Download App';
$show_back = true;

$app_version = get_setting('app_version', '1.0.0');
$app_size = get_setting('app_size', '12 MB');
$app_download_url = get_setting('app_download_url', '#');
$app_changelog = get_setting('app_changelog', 'Bug fixes dan peningkatan performa.');

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Download Aplikasi</h2>
    </div>

    <div class="glassmorphism download-card">
        <div class="app-icon-large">
            <img src="<?= BASE_URL ?>/assets/img/pwa/icon-192.svg" alt="NOXARA App" width="80" height="80">
        </div>
        <h3>NOXARA Mobile</h3>
        <p class="app-tagline"><?= sanitize(SITE_TAGLINE) ?></p>

        <div class="app-info-grid">
            <div class="app-info-item">
                <span class="label">Versi</span>
                <span class="value"><?= sanitize($app_version) ?></span>
            </div>
            <div class="app-info-item">
                <span class="label">Ukuran</span>
                <span class="value"><?= sanitize($app_size) ?></span>
            </div>
            <div class="app-info-item">
                <span class="label">Platform</span>
                <span class="value">Android</span>
            </div>
        </div>

        <a href="<?= sanitize($app_download_url) ?>" class="btn btn-primary btn-block btn-lg" download>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download APK
        </a>

        <div class="app-changelog">
            <h4>What's New</h4>
            <p><?= sanitize($app_changelog) ?></p>
        </div>
    </div>

    <div class="glassmorphism install-guide">
        <h4>Cara Install</h4>
        <ol class="install-steps">
            <li>Download file APK di atas</li>
            <li>Buka file APK yang telah didownload</li>
            <li>Izinkan instalasi dari sumber tidak dikenal jika diminta</li>
            <li>Ikuti proses instalasi hingga selesai</li>
            <li>Buka aplikasi dan login dengan akun NOXARA kamu</li>
        </ol>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

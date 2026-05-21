<?php
/**
 * NOXARA - My Packages
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Paket Saya';
$show_back = true;

// Get user packages
$stmt = db()->prepare("
    SELECT up.*, p.name as product_name, p.image, p.daily_profit as base_profit, p.duration_days as pkg_duration
    FROM user_products up 
    JOIN products p ON p.id = up.product_id 
    WHERE up.user_id = ? AND up.status IN ('active','completed')
    ORDER BY up.status ASC, up.created_at DESC
");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Paket Saya</h2>
        <span class="badge-count"><?= count($packages) ?> Paket</span>
    </div>

    <?php if (empty($packages)): ?>
    <div class="empty-state">
        <img src="<?= BASE_URL ?>/assets/img/illustrations/empty-state.svg" alt="Kosong" width="120">
        <p>Belum ada paket yang dibeli.</p>
        <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-primary">Beli Paket</a>
    </div>
    <?php else: ?>
    <div class="package-list">
        <?php foreach ($packages as $pkg): ?>
        <?php
            $days_left = max(0, $pkg['duration_days'] - $pkg['days_claimed']);
            $progress = $pkg['duration_days'] > 0 ? round(($pkg['days_claimed'] / $pkg['duration_days']) * 100) : 0;
        ?>
        <div class="glassmorphism card-package <?= $pkg['status'] === 'completed' ? 'card-completed' : '' ?>">
            <div class="card-package-header">
                <div class="card-package-info">
                    <h3><?= sanitize($pkg['product_name']) ?></h3>
                    <span class="badge badge-<?= $pkg['status'] === 'active' ? 'success' : 'secondary' ?>">
                        <?= $pkg['status'] === 'active' ? 'Aktif' : 'Selesai' ?>
                    </span>
                </div>
                <div class="card-package-profit">
                    <span class="label">Profit/hari</span>
                    <span class="value"><?= format_rupiah($pkg['daily_profit']) ?></span>
                </div>
            </div>
            <div class="card-package-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                </div>
                <div class="progress-info">
                    <span>Hari <?= $pkg['days_claimed'] ?>/<?= $pkg['duration_days'] ?></span>
                    <span><?= $days_left ?> hari tersisa</span>
                </div>
            </div>
            <div class="card-package-footer">
                <div class="stat-item">
                    <span class="label">Total Earned</span>
                    <span class="value text-success"><?= format_rupiah($pkg['total_earned']) ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Mulai</span>
                    <span class="value"><?= date_id($pkg['created_at']) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

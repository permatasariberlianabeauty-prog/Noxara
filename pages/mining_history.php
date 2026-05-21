<?php
/**
 * NOXARA - Mining History
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Riwayat Mining';
$show_back = true;

// Get mining logs
$stmt = db()->prepare("
    SELECT ml.*, p.name as product_name 
    FROM mining_logs ml 
    JOIN user_products up ON up.id = ml.user_product_id 
    JOIN products p ON p.id = up.product_id 
    WHERE ml.user_id = ? 
    ORDER BY ml.mining_date DESC 
    LIMIT 60
");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group by date
$grouped = [];
foreach ($logs as $log) {
    $date = $log['mining_date'];
    $grouped[$date][] = $log;
}

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Riwayat Mining</h2>
    </div>

    <?php if (empty($grouped)): ?>
    <div class="empty-state">
        <img src="<?= BASE_URL ?>/assets/img/illustrations/empty-state.svg" alt="Kosong" width="120">
        <p>Belum ada riwayat mining.</p>
    </div>
    <?php else: ?>
    <div class="mining-history-list">
        <?php foreach ($grouped as $date => $items): ?>
        <div class="history-date-group">
            <div class="history-date-header"><?= date_id($date) ?></div>
            <?php foreach ($items as $item): ?>
            <div class="history-item glassmorphism">
                <div class="history-item-info">
                    <span class="history-product"><?= sanitize($item['product_name']) ?></span>
                    <span class="history-amount text-success">+<?= format_rupiah($item['amount'] ?? 0) ?></span>
                </div>
                <span class="badge badge-<?= ($item['status'] ?? 'completed') === 'completed' ? 'success' : 'danger' ?>">
                    <?= ($item['status'] ?? 'completed') === 'completed' ? 'Completed' : 'Missed' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

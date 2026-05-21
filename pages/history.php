<?php
/**
 * NOXARA - Transaction History
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Riwayat Transaksi';
$show_back = true;

$filter = $_GET['filter'] ?? 'all';
$valid_filters = ['all', 'topup', 'withdraw', 'mining', 'bonus', 'referral'];
if (!in_array($filter, $valid_filters)) $filter = 'all';

// Build query
$sql = "SELECT * FROM transactions WHERE user_id = ?";
$params = [$user['id']];
$types = 'i';

if ($filter !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $filter;
    $types .= 's';
}
$sql .= " ORDER BY created_at DESC LIMIT 100";

$stmt = db()->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Riwayat Transaksi</h2>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs scrollable">
        <?php
        $filter_labels = [
            'all' => 'Semua',
            'topup' => 'Top Up',
            'withdraw' => 'Withdraw',
            'mining' => 'Mining',
            'bonus' => 'Bonus',
            'referral' => 'Referral'
        ];
        foreach ($filter_labels as $key => $label): ?>
        <a href="<?= BASE_URL ?>/pages/history.php?filter=<?= $key ?>" 
           class="filter-tab <?= $filter === $key ? 'active' : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($transactions)): ?>
    <div class="empty-state">
        <img src="<?= BASE_URL ?>/assets/img/illustrations/empty-state.svg" alt="Kosong" width="120">
        <p>Belum ada transaksi.</p>
    </div>
    <?php else: ?>
    <div class="transaction-list">
        <?php foreach ($transactions as $tx): ?>
        <?php
            $is_credit = in_array($tx['type'], ['topup', 'mining', 'bonus', 'referral']);
            $icon_color = $is_credit ? '#00D4FF' : '#FF6B6B';
            $sign = $is_credit ? '+' : '-';
        ?>
        <div class="transaction-item glassmorphism">
            <div class="tx-icon" style="color: <?= $icon_color ?>">
                <?php if ($tx['type'] === 'topup'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
                <?php elseif ($tx['type'] === 'withdraw'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M5 12l7 7 7-7"/></svg>
                <?php elseif ($tx['type'] === 'mining'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/></svg>
                <?php else: ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <?php endif; ?>
            </div>
            <div class="tx-info">
                <span class="tx-desc"><?= sanitize($tx['description'] ?? ucfirst($tx['type'])) ?></span>
                <span class="tx-date"><?= time_ago($tx['created_at']) ?></span>
            </div>
            <div class="tx-amount <?= $is_credit ? 'text-success' : 'text-danger' ?>">
                <?= $sign . format_rupiah(abs($tx['amount'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

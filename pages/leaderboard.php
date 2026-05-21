<?php
/**
 * NOXARA - Leaderboard
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Leaderboard';
$show_back = true;

$tab = $_GET['tab'] ?? 'daily_miners';
$valid_tabs = ['daily_miners', 'weekly_miners', 'monthly_miners', 'referrers'];
if (!in_array($tab, $valid_tabs)) $tab = 'daily_miners';

$leaders = [];
switch ($tab) {
    case 'daily_miners':
        $stmt = db()->query("
            SELECT u.username, u.avatar, COUNT(ml.id) as score 
            FROM mining_logs ml JOIN users u ON u.id = ml.user_id 
            WHERE ml.mining_date = CURDATE() AND ml.status = 'completed'
            GROUP BY ml.user_id ORDER BY score DESC LIMIT 20
        ");
        $leaders = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'weekly_miners':
        $stmt = db()->query("
            SELECT u.username, u.avatar, COUNT(ml.id) as score 
            FROM mining_logs ml JOIN users u ON u.id = ml.user_id 
            WHERE ml.mining_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND ml.status = 'completed'
            GROUP BY ml.user_id ORDER BY score DESC LIMIT 20
        ");
        $leaders = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'monthly_miners':
        $stmt = db()->query("
            SELECT u.username, u.avatar, COUNT(ml.id) as score 
            FROM mining_logs ml JOIN users u ON u.id = ml.user_id 
            WHERE ml.mining_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ml.status = 'completed'
            GROUP BY ml.user_id ORDER BY score DESC LIMIT 20
        ");
        $leaders = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'referrers':
        $stmt = db()->query("
            SELECT u.username, u.avatar, COUNT(r.id) as score 
            FROM users u JOIN users r ON r.referred_by = u.id 
            GROUP BY u.id ORDER BY score DESC LIMIT 20
        ");
        $leaders = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
}

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Leaderboard</h2>
    </div>


    <!-- Tabs -->
    <div class="filter-tabs scrollable">
        <a href="?tab=daily_miners" class="filter-tab <?= $tab === 'daily_miners' ? 'active' : '' ?>">Harian</a>
        <a href="?tab=weekly_miners" class="filter-tab <?= $tab === 'weekly_miners' ? 'active' : '' ?>">Mingguan</a>
        <a href="?tab=monthly_miners" class="filter-tab <?= $tab === 'monthly_miners' ? 'active' : '' ?>">Bulanan</a>
        <a href="?tab=referrers" class="filter-tab <?= $tab === 'referrers' ? 'active' : '' ?>">Top Referrer</a>
    </div>

    <?php if (empty($leaders)): ?>
    <div class="empty-state"><p>Belum ada data leaderboard.</p></div>
    <?php else: ?>
    <div class="leaderboard-list">
        <?php foreach ($leaders as $rank => $leader): ?>
        <div class="leaderboard-item glassmorphism">
            <div class="rank-badge rank-<?= $rank < 3 ? $rank + 1 : 'default' ?>">
                <?= $rank + 1 ?>
            </div>
            <div class="leader-info">
                <span class="leader-name"><?= sanitize(mask_username($leader['username'])) ?></span>
            </div>
            <div class="leader-score">
                <span><?= format_number($leader['score']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

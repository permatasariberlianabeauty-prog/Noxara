<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Leaderboard Settings';
$current_admin_page = 'leaderboard_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['leaderboard_enabled', 'leaderboard_type', 'leaderboard_limit', 'leaderboard_reset_period'];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $stmt->bind_param('sss', $f, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Leaderboard settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$s = [];
$res = db()->query("SELECT setting_key, value FROM settings WHERE setting_key LIKE 'leaderboard_%'");
while ($r = $res->fetch_assoc()) $s[$r['setting_key']] = $r['value'];
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Leaderboard Configuration</h3>
    <form method="post">
        <div class="form-group"><label>Enabled (1=yes, 0=no)</label><input type="number" name="leaderboard_enabled" value="<?= $s['leaderboard_enabled'] ?? 1 ?>" min="0" max="1"></div>
        <div class="form-group"><label>Type (mining, referral, topup)</label><input type="text" name="leaderboard_type" value="<?= htmlspecialchars($s['leaderboard_type'] ?? 'mining') ?>"></div>
        <div class="form-group"><label>Display Limit</label><input type="number" name="leaderboard_limit" value="<?= $s['leaderboard_limit'] ?? 20 ?>"></div>
        <div class="form-group"><label>Reset Period (daily, weekly, monthly)</label><input type="text" name="leaderboard_reset_period" value="<?= htmlspecialchars($s['leaderboard_reset_period'] ?? 'weekly') ?>"></div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

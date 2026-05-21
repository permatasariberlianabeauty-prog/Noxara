<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Daily Bonus Settings';
$current_admin_page = 'daily_bonus_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($day = 1; $day <= 7; $day++) {
        $reward = (float)$_POST["day_$day"];
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $key = "daily_bonus_day_$day";
        $val = (string)$reward;
        $stmt->bind_param('sss', $key, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Daily bonus rewards saved.';
}

include __DIR__ . '/_admin_layout.php';

$rewards = [];
for ($day = 1; $day <= 7; $day++) {
    $r = db()->query("SELECT value FROM settings WHERE setting_key='daily_bonus_day_$day'")->fetch_assoc();
    $rewards[$day] = $r['value'] ?? ($day * 500);
}
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>7-Day Check-in Rewards</h3>
    <form method="post">
        <?php for ($day = 1; $day <= 7; $day++): ?>
        <div class="form-group"><label>Day <?= $day ?> Reward (Rp)</label><input type="number" name="day_<?= $day ?>" value="<?= $rewards[$day] ?>"></div>
        <?php endfor; ?>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Commission Settings';
$current_admin_page = 'commission_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $l1 = (float)$_POST['l1'];
    $l2 = (float)$_POST['l2'];
    $l3 = (float)$_POST['l3'];
    db()->query("UPDATE settings SET value='$l1' WHERE setting_key='referral_l1_percent'");
    db()->query("UPDATE settings SET value='$l2' WHERE setting_key='referral_l2_percent'");
    db()->query("UPDATE settings SET value='$l3' WHERE setting_key='referral_l3_percent'");
    $msg = 'Commission settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$l1 = db()->query("SELECT value FROM settings WHERE setting_key='referral_l1_percent'")->fetch_assoc()['value'] ?? REFERRAL_L1_PERCENT;
$l2 = db()->query("SELECT value FROM settings WHERE setting_key='referral_l2_percent'")->fetch_assoc()['value'] ?? REFERRAL_L2_PERCENT;
$l3 = db()->query("SELECT value FROM settings WHERE setting_key='referral_l3_percent'")->fetch_assoc()['value'] ?? REFERRAL_L3_PERCENT;
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Referral Commission Percentages</h3>
    <form method="post">
        <div class="form-group"><label>Level 1 (%)</label><input type="number" name="l1" step="0.01" value="<?= $l1 ?>"></div>
        <div class="form-group"><label>Level 2 (%)</label><input type="number" name="l2" step="0.01" value="<?= $l2 ?>"></div>
        <div class="form-group"><label>Level 3 (%)</label><input type="number" name="l3" step="0.01" value="<?= $l3 ?>"></div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

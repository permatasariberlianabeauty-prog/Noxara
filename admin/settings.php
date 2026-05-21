<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Settings';
$current_admin_page = 'settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $val) {
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $stmt->bind_param('sss', $key, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$keys = ['site_name','site_tagline','whatsapp_link','total_members_display','total_paid_display','total_mining_display','maintenance_mode','registration_enabled'];
$settings = [];
$res = db()->query("SELECT setting_key, value FROM settings");
while ($r = $res->fetch_assoc()) $settings[$r['setting_key']] = $r['value'];
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>General Settings</h3>
    <form method="post">
        <?php foreach ($keys as $k): ?>
        <div class="form-group">
            <label><?= htmlspecialchars($k) ?></label>
            <input type="text" name="settings[<?= $k ?>]" value="<?= htmlspecialchars($settings[$k] ?? '') ?>">
        </div>
        <?php endforeach; ?>
        <button type="submit" class="btn-admin primary">Save All</button>
    </form>
</div>

<div class="card">
    <h3>All Settings (Key-Value)</h3>
    <table>
        <tr><th>Key</th><th>Value</th></tr>
        <?php foreach ($settings as $k => $v): ?>
        <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars(substr($v, 0, 100)) ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

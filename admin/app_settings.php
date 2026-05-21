<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'App Settings';
$current_admin_page = 'app_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['app_version', 'app_download_url', 'app_changelog', 'app_force_update'];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $stmt->bind_param('sss', $f, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'App settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$s = [];
$res = db()->query("SELECT setting_key, value FROM settings WHERE setting_key LIKE 'app_%'");
while ($r = $res->fetch_assoc()) $s[$r['setting_key']] = $r['value'];
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>App Version & Download</h3>
    <form method="post">
        <div class="form-group"><label>App Version</label><input type="text" name="app_version" value="<?= htmlspecialchars($s['app_version'] ?? '1.0.0') ?>"></div>
        <div class="form-group"><label>Download URL</label><input type="text" name="app_download_url" value="<?= htmlspecialchars($s['app_download_url'] ?? '') ?>"></div>
        <div class="form-group"><label>Force Update (1=yes, 0=no)</label><input type="number" name="app_force_update" value="<?= $s['app_force_update'] ?? 0 ?>" min="0" max="1"></div>
        <div class="form-group"><label>Changelog</label><textarea name="app_changelog" rows="5"><?= htmlspecialchars($s['app_changelog'] ?? '') ?></textarea></div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

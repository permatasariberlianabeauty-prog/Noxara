<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'QRIS Settings';
$current_admin_page = 'qris_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['cashify_api_key', 'cashify_qris_id', 'cashify_package_ids', 'cashify_expired_minutes'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $stmt->bind_param('sss', $f, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'QRIS settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$s = [];
$res = db()->query("SELECT setting_key, value FROM settings WHERE setting_key LIKE 'cashify_%'");
while ($r = $res->fetch_assoc()) $s[$r['setting_key']] = $r['value'];
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Cashify QRIS Configuration</h3>
    <form method="post">
        <div class="form-group"><label>API Key</label><input type="text" name="cashify_api_key" value="<?= htmlspecialchars($s['cashify_api_key'] ?? CASHIFY_API_KEY) ?>"></div>
        <div class="form-group"><label>QRIS ID</label><input type="text" name="cashify_qris_id" value="<?= htmlspecialchars($s['cashify_qris_id'] ?? CASHIFY_QRIS_ID) ?>"></div>
        <div class="form-group"><label>Package IDs (JSON)</label><input type="text" name="cashify_package_ids" value="<?= htmlspecialchars($s['cashify_package_ids'] ?? CASHIFY_PACKAGE_IDS) ?>"></div>
        <div class="form-group"><label>Expired Minutes</label><input type="number" name="cashify_expired_minutes" value="<?= htmlspecialchars($s['cashify_expired_minutes'] ?? CASHIFY_EXPIRED_MINUTES) ?>"></div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

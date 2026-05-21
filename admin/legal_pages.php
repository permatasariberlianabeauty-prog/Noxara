<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Legal Pages';
$current_admin_page = 'legal_pages';

$msg = '';
$pages_keys = ['terms', 'privacy', 'about'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($pages_keys as $k) {
        $val = $_POST[$k] ?? '';
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $key = 'legal_' . $k;
        $stmt->bind_param('sss', $key, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Legal pages saved.';
}

include __DIR__ . '/_admin_layout.php';

$content = [];
foreach ($pages_keys as $k) {
    $r = db()->query("SELECT value FROM settings WHERE setting_key='legal_$k'")->fetch_assoc();
    $content[$k] = $r['value'] ?? '';
}
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Edit Legal Pages</h3>
    <form method="post">
        <div class="form-group"><label>Terms & Conditions</label><textarea name="terms" rows="8"><?= htmlspecialchars($content['terms']) ?></textarea></div>
        <div class="form-group"><label>Privacy Policy</label><textarea name="privacy" rows="8"><?= htmlspecialchars($content['privacy']) ?></textarea></div>
        <div class="form-group"><label>About Us</label><textarea name="about" rows="8"><?= htmlspecialchars($content['about']) ?></textarea></div>
        <button type="submit" class="btn-admin primary">Save All</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

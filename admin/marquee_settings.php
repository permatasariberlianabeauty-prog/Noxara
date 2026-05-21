<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Marquee Settings';
$current_admin_page = 'marquee_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['marquee_text']);
    $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES ('marquee_text',?) ON DUPLICATE KEY UPDATE value=?");
    $stmt->bind_param('ss', $text, $text);
    $stmt->execute(); $stmt->close();
    $msg = 'Marquee text saved.';
}

include __DIR__ . '/_admin_layout.php';
$current = db()->query("SELECT value FROM settings WHERE setting_key='marquee_text'")->fetch_assoc()['value'] ?? '';
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Marquee Text</h3>
    <form method="post">
        <div class="form-group"><label>Text (shown as scrolling ticker)</label><textarea name="marquee_text" rows="4"><?= htmlspecialchars($current) ?></textarea></div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

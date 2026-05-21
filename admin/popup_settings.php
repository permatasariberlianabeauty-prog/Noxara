<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Popup Settings';
$current_admin_page = 'popup_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['popup_title', 'popup_message', 'popup_button_text', 'popup_link', 'popup_mode'];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?");
        $stmt->bind_param('sss', $f, $val, $val);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Popup settings saved.';
}

include __DIR__ . '/_admin_layout.php';

$s = [];
$res = db()->query("SELECT setting_key, value FROM settings WHERE setting_key LIKE 'popup_%'");
while ($row = $res->fetch_assoc()) $s[$row['setting_key']] = $row['value'];
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Popup Configuration</h3>
    <form method="post">
        <div class="form-group"><label>Title</label><input type="text" name="popup_title" value="<?= htmlspecialchars($s['popup_title'] ?? '') ?>"></div>
        <div class="form-group"><label>Message</label><textarea name="popup_message"><?= htmlspecialchars($s['popup_message'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Button Text</label><input type="text" name="popup_button_text" value="<?= htmlspecialchars($s['popup_button_text'] ?? '') ?>"></div>
        <div class="form-group"><label>Link</label><input type="text" name="popup_link" value="<?= htmlspecialchars($s['popup_link'] ?? '') ?>"></div>
        <div class="form-group"><label>Mode</label>
            <select name="popup_mode">
                <option value="always" <?= ($s['popup_mode'] ?? '') === 'always' ? 'selected' : '' ?>>Always</option>
                <option value="once" <?= ($s['popup_mode'] ?? '') === 'once' ? 'selected' : '' ?>>Once per session</option>
                <option value="disabled" <?= ($s['popup_mode'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
            </select>
        </div>
        <button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

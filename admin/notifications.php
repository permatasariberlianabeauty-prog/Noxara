<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Notifications';
$current_admin_page = 'notifications';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $user_id = (int)($_POST['user_id'] ?? 0);
    $type = $_POST['type'] ?? 'info';

    if ($user_id > 0) {
        $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->bind_param('isss', $user_id, $title, $message, $type);
        $stmt->execute(); $stmt->close();
        $msg = 'Notification sent to user #' . $user_id;
    } else {
        $users = db()->query("SELECT id FROM users WHERE status='active'")->fetch_all(MYSQLI_ASSOC);
        $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?,?,?,?,NOW())");
        foreach ($users as $u) {
            $stmt->bind_param('isss', $u['id'], $title, $message, $type);
            $stmt->execute();
        }
        $stmt->close();
        $msg = 'Broadcast sent to ' . count($users) . ' users.';
    }
}

include __DIR__ . '/_admin_layout.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Send Notification</h3>
    <form method="post">
        <div class="form-group"><label>User ID (0 = broadcast to all)</label><input type="number" name="user_id" value="0" min="0"></div>
        <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Message</label><textarea name="message" required></textarea></div>
        <div class="form-group"><label>Type</label>
            <select name="type"><option value="info">Info</option><option value="success">Success</option><option value="warning">Warning</option><option value="promo">Promo</option></select>
        </div>
        <button type="submit" class="btn-admin primary">Send</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

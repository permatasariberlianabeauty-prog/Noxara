<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Chat Moderation';
$current_admin_page = 'chat';

$msg = '';
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM chat_messages WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    $msg = 'Message deleted.';
}
if (isset($_GET['pin'])) {
    $stmt = db()->prepare("UPDATE chat_messages SET is_pinned=1 WHERE id=?");
    $id = (int)$_GET['pin'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    $msg = 'Message pinned.';
}
if (isset($_GET['unpin'])) {
    $stmt = db()->prepare("UPDATE chat_messages SET is_pinned=0 WHERE id=?");
    $id = (int)$_GET['unpin'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    $msg = 'Message unpinned.';
}

include __DIR__ . '/_admin_layout.php';
$messages = db()->query("SELECT cm.*, u.username FROM chat_messages cm LEFT JOIN users u ON u.id=cm.user_id ORDER BY cm.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Community Chat Messages</h3>
    <table>
        <tr><th>ID</th><th>User</th><th>Message</th><th>Pinned</th><th>Date</th><th>Actions</th></tr>
        <?php foreach ($messages as $m): ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['username'] ?? 'Unknown') ?></td>
            <td><?= htmlspecialchars(substr($m['message'] ?? '', 0, 80)) ?></td>
            <td><?= !empty($m['is_pinned']) ? '<span class="badge badge-info">Pinned</span>' : '-' ?></td>
            <td><?= date('d/m H:i', strtotime($m['created_at'])) ?></td>
            <td style="white-space:nowrap;">
                <?php if (empty($m['is_pinned'])): ?>
                    <a href="?pin=<?= $m['id'] ?>" class="btn-admin primary">Pin</a>
                <?php else: ?>
                    <a href="?unpin=<?= $m['id'] ?>" class="btn-admin">Unpin</a>
                <?php endif; ?>
                <a href="?delete=<?= $m['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete message?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

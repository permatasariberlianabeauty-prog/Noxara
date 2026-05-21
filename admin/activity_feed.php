<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Activity Feed';
$current_admin_page = 'activity_feed';

if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM activity_feed WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/activity_feed.php'); exit;
}

include __DIR__ . '/_admin_layout.php';
$entries = db()->query("SELECT af.*, u.username FROM activity_feed af LEFT JOIN users u ON u.id=af.user_id ORDER BY af.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <h3>Recent Activity</h3>
    <table>
        <tr><th>ID</th><th>User</th><th>Type</th><th>Message</th><th>Date</th><th>Actions</th></tr>
        <?php foreach ($entries as $e): ?>
        <tr>
            <td><?= $e['id'] ?></td>
            <td><?= htmlspecialchars($e['username'] ?? 'System') ?></td>
            <td><span class="badge badge-info"><?= htmlspecialchars($e['type'] ?? '') ?></span></td>
            <td><?= htmlspecialchars($e['message'] ?? '') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
            <td><a href="?delete=<?= $e['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($entries)): ?><tr><td colspan="6" style="text-align:center;color:#888;">No activity yet.</td></tr><?php endif; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

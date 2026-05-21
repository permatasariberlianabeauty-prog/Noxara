<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Achievements';
$current_admin_page = 'achievements';

$msg = '';
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM achievements WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/achievements.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $type = trim($_POST['type']);
    $target = (int)$_POST['target'];
    $reward = (float)$_POST['reward'];
    $icon = trim($_POST['icon'] ?? '');

    if ($id) {
        $stmt = db()->prepare("UPDATE achievements SET name=?,description=?,type=?,target_value=?,reward_amount=?,icon=? WHERE id=?");
        $stmt->bind_param('sssidsi', $name, $description, $type, $target, $reward, $icon, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO achievements (name,description,type,target_value,reward_amount,icon,created_at) VALUES(?,?,?,?,?,?,NOW())");
        $stmt->bind_param('sssids', $name, $description, $type, $target, $reward, $icon);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Achievement saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM achievements WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$achievements = db()->query("SELECT * FROM achievements ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Achievement' : 'Add Achievement' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Type (mining, topup, referral, etc)</label><input type="text" name="type" value="<?= htmlspecialchars($edit['type'] ?? '') ?>" required></div>
        <div class="form-group"><label>Target Value</label><input type="number" name="target" value="<?= $edit['target_value'] ?? 1 ?>"></div>
        <div class="form-group"><label>Reward Amount (Rp)</label><input type="number" name="reward" value="<?= $edit['reward_amount'] ?? 0 ?>"></div>
        <div class="form-group"><label>Icon (emoji or URL)</label><input type="text" name="icon" value="<?= htmlspecialchars($edit['icon'] ?? '') ?>"></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Achievements</h3>
    <table>
        <tr><th>ID</th><th>Name</th><th>Type</th><th>Target</th><th>Reward</th><th>Actions</th></tr>
        <?php foreach ($achievements as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td><?= $a['type'] ?></td>
            <td><?= $a['target_value'] ?></td>
            <td><?= format_rupiah($a['reward_amount']) ?></td>
            <td>
                <a href="?edit=<?= $a['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $a['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

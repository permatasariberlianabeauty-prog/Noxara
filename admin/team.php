<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Team Members';
$current_admin_page = 'team';

$msg = '';
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM team_members WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/team.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $photo = $_POST['existing_photo'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fname = 'team_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], UPLOADS_PATH . '/team/' . $fname);
        $photo = '/uploads/team/' . $fname;
    }

    if ($id) {
        $stmt = db()->prepare("UPDATE team_members SET name=?,role=?,photo=?,sort_order=? WHERE id=?");
        $stmt->bind_param('sssii', $name, $role, $photo, $sort_order, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO team_members (name,role,photo,sort_order,created_at) VALUES(?,?,?,?,NOW())");
        $stmt->bind_param('sssi', $name, $role, $photo, $sort_order);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Team member saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM team_members WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$members = db()->query("SELECT * FROM team_members ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Member' : 'Add Member' ?></h3>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($edit['photo'] ?? '') ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Role</label><input type="text" name="role" value="<?= htmlspecialchars($edit['role'] ?? '') ?>" required></div>
        <div class="form-group"><label>Photo</label><input type="file" name="photo" accept="image/*"></div>
        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $edit['sort_order'] ?? 0 ?>"></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Team Members</h3>
    <table>
        <tr><th>ID</th><th>Photo</th><th>Name</th><th>Role</th><th>Order</th><th>Actions</th></tr>
        <?php foreach ($members as $m): ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?php if ($m['photo']): ?><img src="<?= BASE_URL . $m['photo'] ?>" style="height:30px;border-radius:50%;"><?php else: ?>-<?php endif; ?></td>
            <td><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['role']) ?></td>
            <td><?= $m['sort_order'] ?></td>
            <td>
                <a href="?edit=<?= $m['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $m['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

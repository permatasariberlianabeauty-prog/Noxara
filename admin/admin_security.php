<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Admin Users';
$current_admin_page = 'admin_security';

$msg = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['admin_id']) {
        $stmt = db()->prepare("DELETE FROM admin_users WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    }
    header('Location: ' . BASE_URL . '/admin/admin_security.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $status = $_POST['status'];

    if ($id) {
        $stmt = db()->prepare("UPDATE admin_users SET username=?, full_name=?, role=?, status=? WHERE id=?");
        $stmt->bind_param('ssssi', $username, $full_name, $role, $status, $id);
        $stmt->execute(); $stmt->close();
        if (!empty($_POST['password'])) {
            $pw = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmt = db()->prepare("UPDATE admin_users SET password=? WHERE id=?");
            $stmt->bind_param('si', $pw, $id); $stmt->execute(); $stmt->close();
        }
    } else {
        $pw = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = db()->prepare("INSERT INTO admin_users (username, password, full_name, role, status, created_at) VALUES (?,?,?,?,?,NOW())");
        $stmt->bind_param('sssss', $username, $pw, $full_name, $role, $status);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'Admin user saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM admin_users WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$admins = db()->query("SELECT * FROM admin_users ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Admin' : 'Add Admin' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($edit['username'] ?? '') ?>" required></div>
        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?= htmlspecialchars($edit['full_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Password <?= $edit ? '(leave blank to keep)' : '' ?></label><input type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
        <div class="form-group"><label>Role</label>
            <select name="role"><option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option><option value="superadmin" <?= ($edit['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option></select>
        </div>
        <div class="form-group"><label>Status</label>
            <select name="status"><option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select>
        </div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Admin Users</h3>
    <table>
        <tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($admins as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['username']) ?></td>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td><?= $a['role'] ?></td>
            <td><span class="badge badge-<?= $a['status'] === 'active' ? 'success' : 'danger' ?>"><?= $a['status'] ?></span></td>
            <td>
                <a href="?edit=<?= $a['id'] ?>" class="btn-admin primary">Edit</a>
                <?php if ($a['id'] !== (int)$_SESSION['admin_id']): ?>
                <a href="?delete=<?= $a['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete admin?')">Del</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

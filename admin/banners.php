<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Banners';
$current_admin_page = 'banners';

$msg = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM banners WHERE id=?");
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/banners.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $link = trim($_POST['link'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $image_path = $_POST['existing_image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'banner_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_PATH . '/banners/' . $fname);
        $image_path = '/uploads/banners/' . $fname;
    }

    if ($id) {
        $stmt = db()->prepare("UPDATE banners SET image=?, link=?, sort_order=?, status=? WHERE id=?");
        $stmt->bind_param('ssisi', $image_path, $link, $sort_order, $status, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO banners (image, link, sort_order, status, created_at) VALUES(?,?,?,?,NOW())");
        $stmt->bind_param('ssis', $image_path, $link, $sort_order, $status);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Banner saved.';
}

include __DIR__ . '/_admin_layout.php';
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM banners WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$banners = db()->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Banner' : 'Add Banner' ?></h3>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit['image'] ?? '') ?>">
        <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
        <?php if ($edit && $edit['image']): ?><p style="color:#888;font-size:12px;">Current: <?= htmlspecialchars($edit['image']) ?></p><?php endif; ?>
        <div class="form-group"><label>Link URL</label><input type="text" name="link" value="<?= htmlspecialchars($edit['link'] ?? '') ?>"></div>
        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $edit['sort_order'] ?? 0 ?>"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Banners</h3>
    <table>
        <tr><th>ID</th><th>Image</th><th>Link</th><th>Order</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($banners as $b): ?>
        <tr>
            <td><?= $b['id'] ?></td>
            <td><img src="<?= BASE_URL . $b['image'] ?>" style="height:40px;border-radius:4px;"></td>
            <td><?= htmlspecialchars($b['link'] ?? '-') ?></td>
            <td><?= $b['sort_order'] ?></td>
            <td><span class="badge badge-<?= $b['status'] === 'active' ? 'success' : 'warning' ?>"><?= $b['status'] ?></span></td>
            <td>
                <a href="?edit=<?= $b['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $b['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

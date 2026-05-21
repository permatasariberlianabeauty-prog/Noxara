<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Products';
$current_admin_page = 'products';

$msg = '';

// Delete
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM products WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/products.php'); exit;
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $price = (float)$_POST['price'];
    $daily_return = (float)$_POST['daily_return'];
    $duration_days = (int)$_POST['duration_days'];
    $min_vip = (int)$_POST['min_vip'];
    $status = $_POST['status'] ?? 'active';
    $image = $_POST['image'] ?? '';

    if ($id) {
        $stmt = db()->prepare("UPDATE products SET name=?, description=?, price=?, daily_return=?, duration_days=?, min_vip_level=?, status=?, image=? WHERE id=?");
        $stmt->bind_param('ssddiissi', $name, $description, $price, $daily_return, $duration_days, $min_vip, $status, $image, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO products (name, description, price, daily_return, duration_days, min_vip_level, status, image, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param('ssddiiis', $name, $description, $price, $daily_return, $duration_days, $min_vip, $status, $image);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Product saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$products = db()->query("SELECT * FROM products ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3><?= $edit ? 'Edit Product' : 'Add Product' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Price (Rp)</label><input type="number" name="price" value="<?= $edit['price'] ?? '' ?>" required></div>
        <div class="form-group"><label>Daily Return (Rp)</label><input type="number" name="daily_return" step="0.01" value="<?= $edit['daily_return'] ?? '' ?>" required></div>
        <div class="form-group"><label>Duration (days)</label><input type="number" name="duration_days" value="<?= $edit['duration_days'] ?? 30 ?>" required></div>
        <div class="form-group"><label>Min VIP Level</label><input type="number" name="min_vip" value="<?= $edit['min_vip_level'] ?? 0 ?>" min="0" max="3"></div>
        <div class="form-group"><label>Image URL</label><input type="text" name="image" value="<?= htmlspecialchars($edit['image'] ?? '') ?>"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin" style="margin-left:8px;">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Products</h3>
    <table>
        <tr><th>ID</th><th>Name</th><th>Price</th><th>Daily Return</th><th>Days</th><th>VIP</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= format_rupiah($p['price']) ?></td>
            <td><?= format_rupiah($p['daily_return']) ?></td>
            <td><?= $p['duration_days'] ?></td>
            <td><?= $p['min_vip_level'] ?></td>
            <td><span class="badge badge-<?= $p['status'] === 'active' ? 'success' : 'warning' ?>"><?= $p['status'] ?></span></td>
            <td>
                <a href="?edit=<?= $p['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $p['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

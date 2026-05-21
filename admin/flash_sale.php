<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Flash Sales';
$current_admin_page = 'flash_sale';

$msg = '';
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM flash_sales WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/flash_sale.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $product_id = (int)$_POST['product_id'];
    $sale_price = (float)$_POST['sale_price'];
    $start = $_POST['start_at'];
    $end = $_POST['end_at'];
    $max_qty = (int)$_POST['max_qty'];
    $status = $_POST['status'] ?? 'active';

    if ($id) {
        $stmt = db()->prepare("UPDATE flash_sales SET product_id=?,sale_price=?,start_at=?,end_at=?,max_qty=?,status=? WHERE id=?");
        $stmt->bind_param('idssiisi', $product_id, $sale_price, $start, $end, $max_qty, $status, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO flash_sales (product_id,sale_price,start_at,end_at,max_qty,status,created_at) VALUES(?,?,?,?,?,?,NOW())");
        $stmt->bind_param('idsssi', $product_id, $sale_price, $start, $end, $max_qty, $status);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Flash sale saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM flash_sales WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$sales = db()->query("SELECT fs.*, p.name as product_name FROM flash_sales fs LEFT JOIN products p ON p.id=fs.product_id ORDER BY fs.id DESC")->fetch_all(MYSQLI_ASSOC);
$products = db()->query("SELECT id, name FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Flash Sale' : 'Create Flash Sale' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-group"><label>Product</label>
            <select name="product_id">
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($edit['product_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Sale Price (Rp)</label><input type="number" name="sale_price" value="<?= $edit['sale_price'] ?? '' ?>" required></div>
        <div class="form-group"><label>Start</label><input type="datetime-local" name="start_at" value="<?= $edit['start_at'] ?? '' ?>" required></div>
        <div class="form-group"><label>End</label><input type="datetime-local" name="end_at" value="<?= $edit['end_at'] ?? '' ?>" required></div>
        <div class="form-group"><label>Max Qty</label><input type="number" name="max_qty" value="<?= $edit['max_qty'] ?? 10 ?>"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Flash Sales</h3>
    <table>
        <tr><th>ID</th><th>Product</th><th>Sale Price</th><th>Start</th><th>End</th><th>Max</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($sales as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['product_name'] ?? 'N/A') ?></td>
            <td><?= format_rupiah($s['sale_price']) ?></td>
            <td><?= date('d/m H:i', strtotime($s['start_at'])) ?></td>
            <td><?= date('d/m H:i', strtotime($s['end_at'])) ?></td>
            <td><?= $s['max_qty'] ?></td>
            <td><span class="badge badge-<?= $s['status'] === 'active' ? 'success' : 'warning' ?>"><?= $s['status'] ?></span></td>
            <td>
                <a href="?edit=<?= $s['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $s['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

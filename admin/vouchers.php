<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Vouchers';
$current_admin_page = 'vouchers';

$msg = '';
if (isset($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM vouchers WHERE id=?");
    $id = (int)$_GET['delete'];
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    header('Location: ' . BASE_URL . '/admin/vouchers.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = (float)$_POST['value'];
    $min_amount = (float)($_POST['min_amount'] ?? 0);
    $max_uses = (int)$_POST['max_uses'];
    $expires_at = $_POST['expires_at'] ?: null;
    $status = $_POST['status'] ?? 'active';

    if ($id) {
        $stmt = db()->prepare("UPDATE vouchers SET code=?,type=?,value=?,min_amount=?,max_uses=?,expires_at=?,status=? WHERE id=?");
        $stmt->bind_param('ssddissi', $code, $type, $value, $min_amount, $max_uses, $expires_at, $status, $id);
    } else {
        $stmt = db()->prepare("INSERT INTO vouchers (code,type,value,min_amount,max_uses,expires_at,status,created_at) VALUES(?,?,?,?,?,?,?,NOW())");
        $stmt->bind_param('ssddiis', $code, $type, $value, $min_amount, $max_uses, $expires_at, $status);
    }
    $stmt->execute(); $stmt->close();
    $msg = 'Voucher saved.';
}

include __DIR__ . '/_admin_layout.php';

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM vouchers WHERE id=?");
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$vouchers = db()->query("SELECT * FROM vouchers ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>


<div class="card">
    <h3><?= $edit ? 'Edit Voucher' : 'Add Voucher' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-group"><label>Code</label><input type="text" name="code" value="<?= htmlspecialchars($edit['code'] ?? '') ?>" required></div>
        <div class="form-group"><label>Type</label>
            <select name="type">
                <option value="product_discount" <?= ($edit['type'] ?? '') === 'product_discount' ? 'selected' : '' ?>>Product Discount</option>
                <option value="topup_bonus" <?= ($edit['type'] ?? '') === 'topup_bonus' ? 'selected' : '' ?>>Topup Bonus</option>
                <option value="cash_redeem" <?= ($edit['type'] ?? '') === 'cash_redeem' ? 'selected' : '' ?>>Cash Redeem</option>
            </select>
        </div>
        <div class="form-group"><label>Value</label><input type="number" name="value" step="0.01" value="<?= $edit['value'] ?? '' ?>" required></div>
        <div class="form-group"><label>Min Amount</label><input type="number" name="min_amount" value="<?= $edit['min_amount'] ?? 0 ?>"></div>
        <div class="form-group"><label>Max Uses</label><input type="number" name="max_uses" value="<?= $edit['max_uses'] ?? 100 ?>"></div>
        <div class="form-group"><label>Expires At</label><input type="datetime-local" name="expires_at" value="<?= $edit['expires_at'] ?? '' ?>"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($edit['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
        <button type="submit" class="btn-admin primary">Save</button>
        <?php if ($edit): ?><a href="?." class="btn-admin">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>All Vouchers</h3>
    <table>
        <tr><th>ID</th><th>Code</th><th>Type</th><th>Value</th><th>Uses</th><th>Max</th><th>Expires</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($vouchers as $v): ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><strong><?= htmlspecialchars($v['code']) ?></strong></td>
            <td><?= $v['type'] ?></td>
            <td><?= number_format($v['value']) ?></td>
            <td><?= $v['used_count'] ?? 0 ?></td>
            <td><?= $v['max_uses'] ?></td>
            <td><?= $v['expires_at'] ? date('d/m/Y', strtotime($v['expires_at'])) : '-' ?></td>
            <td><span class="badge badge-<?= $v['status'] === 'active' ? 'success' : 'warning' ?>"><?= $v['status'] ?></span></td>
            <td>
                <a href="?edit=<?= $v['id'] ?>" class="btn-admin primary">Edit</a>
                <a href="?delete=<?= $v['id'] ?>" class="btn-admin danger" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

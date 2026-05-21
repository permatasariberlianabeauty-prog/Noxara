<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Wallet Adjustments';
$current_admin_page = 'wallet_adjustments';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $type = $_POST['type']; // credit or debit
    $field = $_POST['field']; // balance or bonus_balance
    $amount = (float)$_POST['amount'];
    $reason = trim($_POST['reason'] ?? '');

    if ($user_id && $amount > 0 && in_array($field, ['balance', 'bonus_balance']) && in_array($type, ['credit', 'debit'])) {
        $op = $type === 'credit' ? '+' : '-';
        $stmt = db()->prepare("UPDATE users SET $field = $field $op ? WHERE id = ?");
        $stmt->bind_param('di', $amount, $user_id); $stmt->execute(); $stmt->close();

        $stmt = db()->prepare("INSERT INTO wallet_adjustments (user_id, type, field_name, amount, reason, admin_id, created_at) VALUES (?,?,?,?,?,?,NOW())");
        $stmt->bind_param('issdsi', $user_id, $type, $field, $amount, $reason, $_SESSION['admin_id']);
        $stmt->execute(); $stmt->close();
        $msg = 'Adjustment applied successfully.';
    } else {
        $msg = 'Invalid input.';
    }
}

include __DIR__ . '/_admin_layout.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
    <h3>Manual Wallet Adjustment</h3>
    <form method="post">
        <div class="form-group">
            <label>User ID</label>
            <input type="number" name="user_id" required>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type">
                <option value="credit">Credit (+)</option>
                <option value="debit">Debit (-)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Field</label>
            <select name="field">
                <option value="balance">Balance</option>
                <option value="bonus_balance">Bonus Balance</option>
            </select>
        </div>
        <div class="form-group">
            <label>Amount (Rp)</label>
            <input type="number" name="amount" step="1" min="1" required>
        </div>
        <div class="form-group">
            <label>Reason</label>
            <textarea name="reason" placeholder="Reason for adjustment..."></textarea>
        </div>
        <button type="submit" class="btn-admin primary">Apply Adjustment</button>
    </form>
</div>

<div class="card">
    <h3>Recent Adjustments</h3>
    <table>
        <tr><th>ID</th><th>User</th><th>Type</th><th>Field</th><th>Amount</th><th>Reason</th><th>Date</th></tr>
        <?php
        $rows = db()->query("SELECT wa.*, u.username FROM wallet_adjustments wa LEFT JOIN users u ON u.id = wa.user_id ORDER BY wa.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['username'] ?? $r['user_id']) ?></td>
            <td><span class="badge badge-<?= $r['type'] === 'credit' ? 'success' : 'danger' ?>"><?= $r['type'] ?></span></td>
            <td><?= $r['field_name'] ?></td>
            <td><?= format_rupiah($r['amount']) ?></td>
            <td><?= htmlspecialchars($r['reason'] ?? '') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

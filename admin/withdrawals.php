<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Withdrawals';
$current_admin_page = 'withdrawals';

// Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $wid = (int)$_GET['id'];
    if ($_GET['action'] === 'approve') {
        $stmt = db()->prepare("UPDATE withdrawals SET status='approved', processed_at=NOW() WHERE id=? AND status='pending'");
        $stmt->bind_param('i', $wid); $stmt->execute(); $stmt->close();
    } elseif ($_GET['action'] === 'reject') {
        // Refund balance
        $stmt = db()->prepare("SELECT user_id, amount FROM withdrawals WHERE id=? AND status='pending'");
        $stmt->bind_param('i', $wid); $stmt->execute();
        $wd = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($wd) {
            $stmt = db()->prepare("UPDATE withdrawals SET status='rejected', processed_at=NOW() WHERE id=?");
            $stmt->bind_param('i', $wid); $stmt->execute(); $stmt->close();
            $stmt = db()->prepare("UPDATE users SET balance = balance + ? WHERE id=?");
            $stmt->bind_param('di', $wd['amount'], $wd['user_id']); $stmt->execute(); $stmt->close();
        }
    }
    header('Location: ' . BASE_URL . '/admin/withdrawals.php'); exit;
}

include __DIR__ . '/_admin_layout.php';

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE w.status = '" . db()->real_escape_string($status_filter) . "'" : '';

$rows = db()->query("SELECT w.*, u.username, b.bank_name, b.account_number, b.account_name FROM withdrawals w JOIN users u ON u.id = w.user_id LEFT JOIN bank_accounts b ON b.id = w.bank_account_id $where ORDER BY w.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div style="margin-bottom:16px;display:flex;gap:8px;">
        <a href="?status=" class="btn-admin <?= !$status_filter ? 'primary' : '' ?>">All</a>
        <a href="?status=pending" class="btn-admin <?= $status_filter === 'pending' ? 'primary' : '' ?>">Pending</a>
        <a href="?status=approved" class="btn-admin <?= $status_filter === 'approved' ? 'primary' : '' ?>">Approved</a>
        <a href="?status=rejected" class="btn-admin <?= $status_filter === 'rejected' ? 'primary' : '' ?>">Rejected</a>
    </div>
    <table>
        <tr><th>ID</th><th>User</th><th>Amount</th><th>Fee</th><th>Net</th><th>Bank</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        <?php foreach ($rows as $w): ?>
        <tr>
            <td><?= $w['id'] ?></td>
            <td><?= htmlspecialchars($w['username']) ?></td>
            <td><?= format_rupiah($w['amount']) ?></td>
            <td><?= format_rupiah($w['fee']) ?></td>
            <td><?= format_rupiah($w['net_amount']) ?></td>
            <td><?= htmlspecialchars(($w['bank_name'] ?? '') . ' - ' . ($w['account_number'] ?? '')) ?></td>
            <td><span class="badge badge-<?= $w['status'] === 'approved' ? 'success' : ($w['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $w['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($w['created_at'])) ?></td>
            <td>
                <?php if ($w['status'] === 'pending'): ?>
                    <a href="?action=approve&id=<?= $w['id'] ?>" class="btn-admin success" onclick="return confirm('Approve?')">Approve</a>
                    <a href="?action=reject&id=<?= $w['id'] ?>" class="btn-admin danger" onclick="return confirm('Reject & refund?')">Reject</a>
                <?php else: ?>
                    -
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

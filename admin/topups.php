<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Top Ups';
$current_admin_page = 'topups';
include __DIR__ . '/_admin_layout.php';

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE t.status = '" . db()->real_escape_string($status_filter) . "'" : '';

$rows = db()->query("SELECT t.*, u.username FROM topups t JOIN users u ON u.id = t.user_id $where ORDER BY t.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div style="margin-bottom:16px;display:flex;gap:8px;">
        <a href="?status=" class="btn-admin <?= !$status_filter ? 'primary' : '' ?>">All</a>
        <a href="?status=pending" class="btn-admin <?= $status_filter === 'pending' ? 'primary' : '' ?>">Pending</a>
        <a href="?status=paid" class="btn-admin <?= $status_filter === 'paid' ? 'primary' : '' ?>">Paid</a>
        <a href="?status=expired" class="btn-admin <?= $status_filter === 'expired' ? 'primary' : '' ?>">Expired</a>
        <a href="?status=cancelled" class="btn-admin <?= $status_filter === 'cancelled' ? 'primary' : '' ?>">Cancelled</a>
    </div>
    <table>
        <tr><th>ID</th><th>User</th><th>Amount</th><th>Unique Code</th><th>Total</th><th>Status</th><th>Created</th><th>Paid At</th></tr>
        <?php foreach ($rows as $t): ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td><?= format_rupiah($t['amount'] ?? 0) ?></td>
            <td><?= $t['unique_code'] ?? '-' ?></td>
            <td><?= format_rupiah($t['total_amount'] ?? 0) ?></td>
            <td><span class="badge badge-<?= $t['status'] === 'paid' ? 'success' : ($t['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $t['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
            <td><?= $t['paid_at'] ? date('d/m/Y H:i', strtotime($t['paid_at'])) : '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

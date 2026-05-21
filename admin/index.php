<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Dashboard';
$current_admin_page = 'dashboard';
include __DIR__ . '/_admin_layout.php';

// Stats
$total_users = db()->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_topup = db()->query("SELECT COALESCE(SUM(total_amount),0) as c FROM topups WHERE status='paid'")->fetch_assoc()['c'];
$total_wd = db()->query("SELECT COALESCE(SUM(net_amount),0) as c FROM withdrawals WHERE status='approved'")->fetch_assoc()['c'];
$pending_wd = db()->query("SELECT COUNT(*) as c FROM withdrawals WHERE status='pending'")->fetch_assoc()['c'];
$active_packages = db()->query("SELECT COUNT(*) as c FROM user_products WHERE status='active'")->fetch_assoc()['c'];
$today_mining = db()->query("SELECT COUNT(*) as c FROM mining_logs WHERE mining_date = CURDATE() AND status='completed'")->fetch_assoc()['c'];
?>

<div class="stats-grid">
    <div class="stat-card"><div class="value"><?= number_format($total_users) ?></div><div class="label">Total Member</div></div>
    <div class="stat-card"><div class="value"><?= format_rupiah($total_topup) ?></div><div class="label">Total Topup (Paid)</div></div>
    <div class="stat-card"><div class="value"><?= format_rupiah($total_wd) ?></div><div class="label">Total Withdraw (Approved)</div></div>
    <div class="stat-card"><div class="value"><?= $pending_wd ?></div><div class="label">Withdraw Pending</div></div>
    <div class="stat-card"><div class="value"><?= $active_packages ?></div><div class="label">Paket Aktif</div></div>
    <div class="stat-card"><div class="value"><?= $today_mining ?></div><div class="label">Mining Hari Ini</div></div>
</div>

<div class="card">
    <h3>Withdraw Pending</h3>
    <?php
    $wds = db()->query("SELECT w.*, u.username, b.bank_name, b.account_number FROM withdrawals w JOIN users u ON u.id = w.user_id JOIN bank_accounts b ON b.id = w.bank_account_id WHERE w.status = 'pending' ORDER BY w.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    ?>
    <table>
        <tr><th>User</th><th>Jumlah</th><th>Fee</th><th>Net</th><th>Bank</th><th>Waktu</th><th>Aksi</th></tr>
        <?php foreach ($wds as $w): ?>
        <tr>
            <td><?= htmlspecialchars($w['username']) ?></td>
            <td><?= format_rupiah($w['amount']) ?></td>
            <td><?= format_rupiah($w['fee']) ?></td>
            <td><strong><?= format_rupiah($w['net_amount']) ?></strong></td>
            <td><?= htmlspecialchars($w['bank_name'] . ' - ' . $w['account_number']) ?></td>
            <td><?= time_ago($w['created_at']) ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/withdrawals.php?action=approve&id=<?= $w['id'] ?>" class="btn-admin success">Approve</a>
                <a href="<?= BASE_URL ?>/admin/withdrawals.php?action=reject&id=<?= $w['id'] ?>" class="btn-admin danger">Reject</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($wds)): ?><tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada withdraw pending.</td></tr><?php endif; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

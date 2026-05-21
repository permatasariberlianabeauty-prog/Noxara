<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Reports';
$current_admin_page = 'reports';

// CSV Export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($type === 'transactions') {
        fputcsv($out, ['ID','User','Type','Amount','Description','Date']);
        $rows = db()->query("SELECT t.*, u.username FROM transactions t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 5000")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $r) fputcsv($out, [$r['id'], $r['username'], $r['type'], $r['amount'], $r['description'] ?? '', $r['created_at']]);
    } elseif ($type === 'topups') {
        fputcsv($out, ['ID','User','Amount','Total','Status','Date']);
        $rows = db()->query("SELECT t.*, u.username FROM topups t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 5000")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $r) fputcsv($out, [$r['id'], $r['username'], $r['amount'] ?? 0, $r['total_amount'] ?? 0, $r['status'], $r['created_at']]);
    } elseif ($type === 'withdrawals') {
        fputcsv($out, ['ID','User','Amount','Fee','Net','Status','Date']);
        $rows = db()->query("SELECT w.*, u.username FROM withdrawals w JOIN users u ON u.id=w.user_id ORDER BY w.created_at DESC LIMIT 5000")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $r) fputcsv($out, [$r['id'], $r['username'], $r['amount'], $r['fee'], $r['net_amount'], $r['status'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

include __DIR__ . '/_admin_layout.php';
?>

<div class="card">
    <h3>Export Reports (CSV)</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
        <a href="?export=transactions" class="btn-admin primary">Export Transactions</a>
        <a href="?export=topups" class="btn-admin primary">Export Top Ups</a>
        <a href="?export=withdrawals" class="btn-admin primary">Export Withdrawals</a>
    </div>
</div>

    </div>
</div>
</body>
</html>

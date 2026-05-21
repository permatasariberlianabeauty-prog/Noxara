<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Cron Status';
$current_admin_page = 'cron_status';
include __DIR__ . '/_admin_layout.php';

$logs = db()->query("SELECT * FROM cron_logs ORDER BY id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <h3>Recent Cron Executions</h3>
    <table>
        <tr><th>ID</th><th>Job</th><th>Status</th><th>Message</th><th>Duration</th><th>Run At</th></tr>
        <?php foreach ($logs as $l): ?>
        <tr>
            <td><?= $l['id'] ?></td>
            <td><?= htmlspecialchars($l['job_name'] ?? '') ?></td>
            <td><span class="badge badge-<?= ($l['status'] ?? '') === 'success' ? 'success' : 'danger' ?>"><?= $l['status'] ?? '' ?></span></td>
            <td><?= htmlspecialchars($l['message'] ?? '') ?></td>
            <td><?= $l['duration_ms'] ?? '-' ?>ms</td>
            <td><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?><tr><td colspan="6" style="text-align:center;color:#888;">No cron logs yet.</td></tr><?php endif; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

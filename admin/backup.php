<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Backup';
$current_admin_page = 'backup';

$msg = '';
if (isset($_GET['trigger'])) {
    $filename = 'backup_' . date('Ymd_His') . '.sql';
    $path = BACKUPS_PATH . '/' . $filename;
    $cmd = sprintf('mysqldump -h%s -u%s -p%s %s > %s 2>&1',
        escapeshellarg(DB_HOST), escapeshellarg(DB_USER), escapeshellarg(DB_PASS), escapeshellarg(DB_NAME), escapeshellarg($path));
    exec($cmd, $output, $ret);

    $status = $ret === 0 ? 'success' : 'failed';
    $size = file_exists($path) ? filesize($path) : 0;
    $stmt = db()->prepare("INSERT INTO backup_logs (filename, file_size, status, created_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param('sis', $filename, $size, $status);
    $stmt->execute(); $stmt->close();
    $msg = $status === 'success' ? "Backup created: $filename" : "Backup failed.";
}

include __DIR__ . '/_admin_layout.php';
$logs = db()->query("SELECT * FROM backup_logs ORDER BY id DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Database Backup</h3>
    <a href="?trigger=1" class="btn-admin primary" onclick="return confirm('Create backup now?')">Trigger Backup Now</a>
</div>

<div class="card">
    <h3>Backup History</h3>
    <table>
        <tr><th>ID</th><th>Filename</th><th>Size</th><th>Status</th><th>Date</th></tr>
        <?php foreach ($logs as $l): ?>
        <tr>
            <td><?= $l['id'] ?></td>
            <td><?= htmlspecialchars($l['filename']) ?></td>
            <td><?= number_format(($l['file_size'] ?? 0) / 1024, 1) ?> KB</td>
            <td><span class="badge badge-<?= $l['status'] === 'success' ? 'success' : 'danger' ?>"><?= $l['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

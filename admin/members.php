<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Members';
$current_admin_page = 'members';

// Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'block':
            $stmt = db()->prepare("UPDATE users SET status='blocked' WHERE id=?");
            $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
            break;
        case 'unblock':
            $stmt = db()->prepare("UPDATE users SET status='active' WHERE id=?");
            $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
            break;
        case 'setvip':
            $vip = isset($_GET['vip']) ? (int)$_GET['vip'] : 0;
            $stmt = db()->prepare("UPDATE users SET vip_level=? WHERE id=?");
            $stmt->bind_param('ii', $vip, $uid); $stmt->execute(); $stmt->close();
            break;
        case 'resetpw':
            $new_pw = password_hash('password123', PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmt = db()->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param('si', $new_pw, $uid); $stmt->execute(); $stmt->close();
            break;
    }
    header('Location: ' . BASE_URL . '/admin/members.php'); exit;
}

include __DIR__ . '/_admin_layout.php';

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = '';
$params = [];
$types = '';
if ($search) {
    $where = "WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";
    $s = "%$search%";
    $params = [$s, $s, $s];
    $types = 'sss';
}

$count_sql = "SELECT COUNT(*) as c FROM users $where";
if ($params) {
    $stmt = db()->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
} else {
    $total = db()->query($count_sql)->fetch_assoc()['c'];
}
$total_pages = ceil($total / $per_page);

$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
if ($params) {
    $params[] = $per_page; $params[] = $offset;
    $types .= 'ii';
    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = db()->prepare($sql);
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="card">
    <form method="get" style="margin-bottom:16px;display:flex;gap:8px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search username/email/phone..." style="flex:1;padding:8px;background:#0A0E1A;border:1px solid #1a2340;border-radius:6px;color:#e0e0e0;">
        <button class="btn-admin primary" type="submit">Search</button>
    </form>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>VIP</th><th>Balance</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-info">VIP <?= $u['vip_level'] ?></span></td>
            <td><?= format_rupiah($u['balance']) ?></td>
            <td><span class="badge badge-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= $u['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td style="white-space:nowrap;">
                <?php if ($u['status'] === 'active'): ?>
                    <a href="?action=block&id=<?= $u['id'] ?>" class="btn-admin danger" onclick="return confirm('Block user?')">Block</a>
                <?php else: ?>
                    <a href="?action=unblock&id=<?= $u['id'] ?>" class="btn-admin success">Unblock</a>
                <?php endif; ?>
                <a href="?action=setvip&id=<?= $u['id'] ?>&vip=1" class="btn-admin primary" title="Set VIP 1">V1</a>
                <a href="?action=setvip&id=<?= $u['id'] ?>&vip=2" class="btn-admin primary" title="Set VIP 2">V2</a>
                <a href="?action=setvip&id=<?= $u['id'] ?>&vip=3" class="btn-admin primary" title="Set VIP 3">V3</a>
                <a href="?action=resetpw&id=<?= $u['id'] ?>" class="btn-admin danger" onclick="return confirm('Reset password to password123?')">PW</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

    </div>
</div>
</body>
</html>

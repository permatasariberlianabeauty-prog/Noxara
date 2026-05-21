<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'VIP Codes';
$current_admin_page = 'vip_codes';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vip_level = (int)$_POST['vip_level'];
    $quantity = (int)$_POST['quantity'];
    $expires_at = $_POST['expires_at'] ?: null;

    for ($i = 0; $i < $quantity; $i++) {
        $code = 'VIP' . $vip_level . '-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = db()->prepare("INSERT INTO vip_codes (code, vip_level, expires_at, created_at) VALUES (?,?,?,NOW())");
        $stmt->bind_param('sis', $code, $vip_level, $expires_at);
        $stmt->execute(); $stmt->close();
    }
    $msg = "$quantity VIP codes generated.";
}

include __DIR__ . '/_admin_layout.php';
$codes = db()->query("SELECT * FROM vip_codes ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Generate VIP Codes</h3>
    <form method="post" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div class="form-group"><label>VIP Level</label><select name="vip_level"><option value="1">VIP 1</option><option value="2">VIP 2</option><option value="3">VIP 3</option></select></div>
        <div class="form-group"><label>Quantity</label><input type="number" name="quantity" value="5" min="1" max="100"></div>
        <div class="form-group"><label>Expires At</label><input type="datetime-local" name="expires_at"></div>
        <button type="submit" class="btn-admin primary" style="height:38px;">Generate</button>
    </form>
</div>

<div class="card">
    <h3>Recent Codes</h3>
    <table>
        <tr><th>Code</th><th>Level</th><th>Used By</th><th>Expires</th><th>Created</th></tr>
        <?php foreach ($codes as $c): ?>
        <tr>
            <td><code><?= htmlspecialchars($c['code']) ?></code></td>
            <td>VIP <?= $c['vip_level'] ?></td>
            <td><?= $c['used_by'] ?? '-' ?></td>
            <td><?= $c['expires_at'] ? date('d/m/Y', strtotime($c['expires_at'])) : 'Never' ?></td>
            <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

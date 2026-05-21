<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'VIP Settings';
$current_admin_page = 'vip_settings';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['levels'] as $level => $data) {
        $stmt = db()->prepare("UPDATE vip_levels SET min_topup=?, min_withdraw=?, withdraw_fee_percent=? WHERE level=?");
        $min_topup = (float)$data['min_topup'];
        $min_wd = (float)$data['min_withdraw'];
        $fee = (float)$data['fee'];
        $lvl = (int)$level;
        $stmt->bind_param('dddi', $min_topup, $min_wd, $fee, $lvl);
        $stmt->execute(); $stmt->close();
    }
    $msg = 'VIP settings saved.';
}

include __DIR__ . '/_admin_layout.php';
$levels = db()->query("SELECT * FROM vip_levels ORDER BY level ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>VIP Level Configuration</h3>
    <form method="post">
        <table>
            <tr><th>Level</th><th>Min Topup</th><th>Min Withdraw</th><th>Withdraw Fee %</th></tr>
            <?php foreach ($levels as $l): ?>
            <tr>
                <td>VIP <?= $l['level'] ?></td>
                <td><input type="number" name="levels[<?= $l['level'] ?>][min_topup]" value="<?= $l['min_topup'] ?>" style="width:150px;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="levels[<?= $l['level'] ?>][min_withdraw]" value="<?= $l['min_withdraw'] ?>" style="width:150px;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="levels[<?= $l['level'] ?>][fee]" value="<?= $l['withdraw_fee_percent'] ?>" step="0.01" style="width:100px;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br><button type="submit" class="btn-admin primary">Save Settings</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Mystery Box';
$current_admin_page = 'mystery_box';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->query("DELETE FROM mystery_box_prizes");
    $names = $_POST['name'];
    $amounts = $_POST['amount'];
    $probs = $_POST['probability'];
    $stmt = db()->prepare("INSERT INTO mystery_box_prizes (name, amount, probability, sort_order) VALUES (?,?,?,?)");
    for ($i = 0; $i < count($names); $i++) {
        if (empty($names[$i])) continue;
        $n = trim($names[$i]);
        $a = (float)$amounts[$i];
        $p = (float)$probs[$i];
        $o = $i + 1;
        $stmt->bind_param('sddi', $n, $a, $p, $o);
        $stmt->execute();
    }
    $stmt->close();

    // Save cost setting
    $cost = (float)$_POST['box_cost'];
    $stmt = db()->prepare("INSERT INTO settings (setting_key, value) VALUES ('mystery_box_cost',?) ON DUPLICATE KEY UPDATE value=?");
    $cv = (string)$cost;
    $stmt->bind_param('ss', $cv, $cv);
    $stmt->execute(); $stmt->close();
    $msg = 'Mystery box settings saved.';
}

include __DIR__ . '/_admin_layout.php';
$prizes = db()->query("SELECT * FROM mystery_box_prizes ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
$cost = db()->query("SELECT value FROM settings WHERE setting_key='mystery_box_cost'")->fetch_assoc()['value'] ?? 5000;
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Mystery Box Settings</h3>
    <form method="post">
        <div class="form-group"><label>Box Cost (Rp)</label><input type="number" name="box_cost" value="<?= $cost ?>"></div>
        <table>
            <tr><th>Prize Name</th><th>Amount (Rp)</th><th>Probability (%)</th></tr>
            <?php for ($i = 0; $i < max(6, count($prizes)); $i++): ?>
            <tr>
                <td><input type="text" name="name[]" value="<?= htmlspecialchars($prizes[$i]['name'] ?? '') ?>" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="amount[]" value="<?= $prizes[$i]['amount'] ?? 0 ?>" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="probability[]" value="<?= $prizes[$i]['probability'] ?? 0 ?>" step="0.01" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
            </tr>
            <?php endfor; ?>
        </table>
        <br><button type="submit" class="btn-admin primary">Save</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Spin Wheel';
$current_admin_page = 'spin_wheel';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear and re-insert
    db()->query("DELETE FROM spin_prizes");
    $names = $_POST['prize_name'];
    $amounts = $_POST['prize_amount'];
    $probs = $_POST['probability'];
    $stmt = db()->prepare("INSERT INTO spin_prizes (name, amount, probability, sort_order) VALUES (?,?,?,?)");
    for ($i = 0; $i < count($names); $i++) {
        if (empty($names[$i])) continue;
        $n = trim($names[$i]);
        $a = (float)$amounts[$i];
        $p = (float)$probs[$i];
        $order = $i + 1;
        $stmt->bind_param('sddi', $n, $a, $p, $order);
        $stmt->execute();
    }
    $stmt->close();
    $msg = 'Spin prizes saved.';
}

include __DIR__ . '/_admin_layout.php';
$prizes = db()->query("SELECT * FROM spin_prizes ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Spin Wheel Prizes</h3>
    <form method="post">
        <table>
            <tr><th>Prize Name</th><th>Amount (Rp)</th><th>Probability (%)</th></tr>
            <?php for ($i = 0; $i < max(8, count($prizes)); $i++): ?>
            <tr>
                <td><input type="text" name="prize_name[]" value="<?= htmlspecialchars($prizes[$i]['name'] ?? '') ?>" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="prize_amount[]" value="<?= $prizes[$i]['amount'] ?? 0 ?>" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
                <td><input type="number" name="probability[]" value="<?= $prizes[$i]['probability'] ?? 0 ?>" step="0.01" style="width:100%;padding:6px;background:#0A0E1A;border:1px solid #1a2340;border-radius:4px;color:#e0e0e0;"></td>
            </tr>
            <?php endfor; ?>
        </table>
        <br><button type="submit" class="btn-admin primary">Save Prizes</button>
    </form>
</div>

    </div>
</div>
</body>
</html>

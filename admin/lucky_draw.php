<?php
require_once __DIR__ . '/../config/bootstrap.php';
define('ADMIN_PAGE', true);
$admin_page_title = 'Lucky Draw';
$current_admin_page = 'lucky_draw';

$msg = '';

// Pick winner
if (isset($_GET['pick_winner']) && isset($_GET['period_id'])) {
    $pid = (int)$_GET['period_id'];
    // Select random eligible user
    $winner = db()->query("SELECT user_id FROM lucky_draw_entries WHERE period_id=$pid ORDER BY RAND() LIMIT 1")->fetch_assoc();
    if ($winner) {
        $stmt = db()->prepare("UPDATE lucky_draw_periods SET winner_user_id=?, status='completed' WHERE id=?");
        $stmt->bind_param('ii', $winner['user_id'], $pid); $stmt->execute(); $stmt->close();
        $msg = "Winner selected: User #" . $winner['user_id'];
    } else {
        $msg = "No entries for this period.";
    }
}

// Create period
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $prize = (float)$_POST['prize_amount'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $stmt = db()->prepare("INSERT INTO lucky_draw_periods (name, prize_amount, start_date, end_date, status, created_at) VALUES (?,?,?,?,'active',NOW())");
    $stmt->bind_param('sdss', $name, $prize, $start, $end);
    $stmt->execute(); $stmt->close();
    $msg = 'Lucky draw period created.';
}

include __DIR__ . '/_admin_layout.php';
$periods = db()->query("SELECT lp.*, u.username as winner_name FROM lucky_draw_periods lp LEFT JOIN users u ON u.id=lp.winner_user_id ORDER BY lp.id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h3>Create Lucky Draw Period</h3>
    <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
        <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Prize Amount</label><input type="number" name="prize_amount" required></div>
        <div class="form-group"><label>Start</label><input type="date" name="start_date" required></div>
        <div class="form-group"><label>End</label><input type="date" name="end_date" required></div>
        <button type="submit" class="btn-admin primary" style="height:38px;">Create</button>
    </form>
</div>

<div class="card">
    <h3>Periods</h3>
    <table>
        <tr><th>ID</th><th>Name</th><th>Prize</th><th>Start</th><th>End</th><th>Winner</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($periods as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= format_rupiah($p['prize_amount']) ?></td>
            <td><?= $p['start_date'] ?></td>
            <td><?= $p['end_date'] ?></td>
            <td><?= $p['winner_name'] ?? '-' ?></td>
            <td><span class="badge badge-<?= $p['status'] === 'active' ? 'success' : 'info' ?>"><?= $p['status'] ?></span></td>
            <td>
                <?php if ($p['status'] === 'active'): ?>
                <a href="?pick_winner=1&period_id=<?= $p['id'] ?>" class="btn-admin success" onclick="return confirm('Pick random winner?')">Pick Winner</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

    </div>
</div>
</body>
</html>

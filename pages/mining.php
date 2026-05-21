<?php
/**
 * NOXARA - Mining Center
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Mining Center';
$show_back = true;
$current_page = '';

// Get active packages
$stmt = db()->prepare("SELECT up.*, p.name as product_name, p.daily_profit as base_profit FROM user_products up JOIN products p ON p.id = up.product_id WHERE up.user_id = ? AND up.status = 'active' ORDER BY up.created_at DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Mining Center</h2>
        <span class="badge-count"><?= count($packages) ?> Paket Aktif</span>
    </div>

    <?php if (empty($packages)): ?>
    <div class="empty-state">
        <img src="<?= BASE_URL ?>/assets/img/illustrations/empty-state.svg" alt="Kosong" width="120">
        <p>Belum ada paket aktif.</p>
        <a href="<?= BASE_URL ?>/pages/products.php" class="btn btn-primary">Beli Paket Sekarang</a>
    </div>
    <?php else: ?>
    <div class="mining-cards">
        <?php foreach ($packages as $pkg): ?>
        <?php
            $today = date('Y-m-d');
            $can_mine = ($pkg['last_mining_date'] !== $today && $pkg['mining_state'] !== 'mining');
            $is_mining = ($pkg['mining_state'] === 'mining');
            $is_done = ($pkg['last_mining_date'] === $today && $pkg['mining_state'] === 'done');
            $finish_time = $pkg['mining_finished_at'] ?? '';
            $can_claim = ($is_mining && $finish_time && strtotime($finish_time) <= time());
        ?>
        <div class="mining-card glassmorphism <?= $is_mining ? 'state-mining' : ($is_done ? 'state-done' : 'state-idle') ?>">
            <div class="mining-card-header">
                <span class="mining-card-name"><?= sanitize($pkg['product_name']) ?></span>
                <span class="mining-card-day">Hari <?= $pkg['days_claimed'] ?>/<?= $pkg['duration_days'] ?></span>
            </div>
            <div class="mining-card-profit">
                <span class="label">Profit/hari</span>
                <span class="value"><?= format_rupiah($pkg['daily_profit']) ?></span>
            </div>
            
            <div class="mining-btn-wrap">
                <?php if ($can_mine): ?>
                <button class="btn-mining pulse-glow" data-id="<?= $pkg['id'] ?>" onclick="startMining(<?= $pkg['id'] ?>)">
                    <span class="mining-btn-text">MINE NOW</span>
                </button>
                <?php elseif ($is_mining && !$can_claim): ?>
                <div class="mining-progress">
                    <div class="progress-ring" data-finish="<?= $finish_time ?>">
                        <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="45"/><circle cx="50" cy="50" r="45" class="progress-fill"/></svg>
                    </div>
                    <span class="countdown-text" data-finish="<?= $finish_time ?>">--:--:--</span>
                </div>
                <?php elseif ($can_claim): ?>
                <button class="btn-mining claim pulse-glow" onclick="claimMining(<?= $pkg['id'] ?>)">
                    <span class="mining-btn-text">KLAIM PROFIT</span>
                </button>
                <?php else: ?>
                <button class="btn-mining done" disabled>
                    <span class="mining-btn-text">Hadir Lagi 00:01</span>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="mining-card-footer">
                <span>Total earned: <?= format_rupiah($pkg['total_earned']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php
$extra_js = '
<script src="' . BASE_URL . '/assets/js/countdown.js"></script>
<script>
async function startMining(id) {
    const btn = event.target.closest(".btn-mining");
    btn.disabled = true;
    btn.innerHTML = "<span class=\"mining-btn-text\">Memulai...</span>";
    try {
        const res = await fetch("' . BASE_URL . '/api/mining.php?action=start_mining", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({user_product_id: id})
        });
        const data = await res.json();
        if (data.success) {
            showToast("Mining dimulai! Profit cair dalam 2 jam.", "success");
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, "error");
            btn.disabled = false;
            btn.innerHTML = "<span class=\"mining-btn-text\">MINE NOW</span>";
        }
    } catch(e) { showToast("Gagal. Coba lagi.", "error"); btn.disabled = false; }
}

async function claimMining(id) {
    const btn = event.target.closest(".btn-mining");
    btn.disabled = true;
    try {
        const res = await fetch("' . BASE_URL . '/api/mining.php?action=claim_one", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({user_product_id: id})
        });
        const data = await res.json();
        if (data.success) {
            showToast("Profit berhasil diklaim!", "success");
            triggerConfetti();
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message, "error");
            btn.disabled = false;
        }
    } catch(e) { showToast("Gagal. Coba lagi.", "error"); btn.disabled = false; }
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

<?php
/**
 * NOXARA - VIP Center
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'VIP Center';
$show_back = true;

$current_vip = (int)($user['vip_level'] ?? 0);

// VIP level config
$vip_levels = [
    0 => ['name' => 'Member', 'min_topup' => 0, 'benefits' => ['Akses mining dasar']],
    1 => ['name' => 'VIP 1', 'min_topup' => 500000, 'benefits' => ['Bonus mining +5%', 'Priority support']],
    2 => ['name' => 'VIP 2', 'min_topup' => 2000000, 'benefits' => ['Bonus mining +10%', 'Exclusive products', 'Faster withdrawal']],
    3 => ['name' => 'VIP 3', 'min_topup' => 5000000, 'benefits' => ['Bonus mining +15%', 'VIP-only products', 'Instant withdrawal', 'Personal manager']],
];

$next_level = min($current_vip + 1, VIP_3);
$current_info = $vip_levels[$current_vip];
$next_info = $vip_levels[$next_level] ?? null;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">VIP Center</h2>
    </div>


    <!-- Current VIP Status -->
    <div class="glassmorphism vip-status-card">
        <div class="vip-badge-display">
            <span class="vip-level-badge vip-<?= $current_vip ?>">VIP <?= $current_vip ?></span>
            <span class="vip-name"><?= sanitize($current_info['name']) ?></span>
        </div>
        <div class="vip-benefits">
            <h4>Benefit Kamu:</h4>
            <ul class="benefit-list">
                <?php foreach ($current_info['benefits'] as $benefit): ?>
                <li><?= sanitize($benefit) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php if ($next_info && $current_vip < VIP_3): ?>
    <!-- Next Level -->
    <div class="glassmorphism vip-next-card">
        <h4>Level Berikutnya: <?= sanitize($next_info['name']) ?></h4>
        <p>Minimum total top up: <?= format_rupiah($next_info['min_topup']) ?></p>
        <div class="vip-next-benefits">
            <h5>Benefit tambahan:</h5>
            <ul class="benefit-list">
                <?php foreach ($next_info['benefits'] as $benefit): ?>
                <li><?= sanitize($benefit) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- VIP Code Redeem -->
    <div class="glassmorphism vip-redeem-card">
        <h4>Redeem Kode VIP</h4>
        <p>Punya kode VIP? Masukkan di bawah untuk upgrade level.</p>
        <div class="form-group">
            <input type="text" id="vipCode" class="form-input"
                   placeholder="Masukkan kode VIP" maxlength="32">
        </div>
        <button onclick="redeemVIP()" class="btn btn-primary btn-block" id="btnVIP">
            Redeem VIP Code
        </button>
    </div>
</section>


<?php
$extra_js = '
<script>
async function redeemVIP() {
    const code = document.getElementById("vipCode").value.trim();
    const btn = document.getElementById("btnVIP");
    if (!code) { showToast("Masukkan kode VIP.", "error"); return; }
    btn.disabled = true;
    btn.textContent = "Memproses...";
    try {
        const res = await fetch("' . BASE_URL . '/api/wallet.php?action=redeem_vip", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({code: code})
        });
        const data = await res.json();
        if (data.success) {
            showToast("VIP berhasil diupgrade!", "success");
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, "error");
        }
    } catch(e) { showToast("Gagal. Coba lagi.", "error"); }
    btn.disabled = false;
    btn.textContent = "Redeem VIP Code";
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

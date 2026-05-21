<?php
/**
 * NOXARA - Withdraw Page
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Tarik Saldo';
$show_back = true;

require_once INCLUDES_PATH . '/wallet.php';
$wallet = get_wallet($user['id']);
$vip_info = db()->prepare("SELECT * FROM vip_levels WHERE level = ?");
$vip_info->bind_param('i', $user['vip_level']);
$vip_info->execute();
$vip = $vip_info->get_result()->fetch_assoc();
$vip_info->close();

// Get bank accounts
$stmt = db()->prepare("SELECT * FROM bank_accounts WHERE user_id = ? ORDER BY is_primary DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$banks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$blocked = (float)$wallet['bonus_balance'] > 0;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <h2 class="section-title">Tarik Saldo</h2>

    <?php if ($blocked): ?>
    <div class="alert alert-warning">
        <strong>Withdraw diblokir.</strong> Anda masih memiliki saldo bonus Rp <?= format_number($wallet['bonus_balance']) ?>. Gunakan saldo bonus terlebih dahulu (untuk membeli paket) sebelum dapat melakukan penarikan.
    </div>
    <?php endif; ?>

    <div class="wd-info glassmorphism">
        <div class="wd-balance">
            <span class="label">Saldo Tersedia</span>
            <span class="value"><?= format_rupiah($wallet['balance']) ?></span>
        </div>
        <div class="wd-rules">
            <div class="rule"><span>Min. Withdraw</span><span><?= format_rupiah($vip['min_withdraw'] ?? 100000) ?></span></div>
            <div class="rule"><span>Fee Admin</span><span><?= ($vip['withdraw_fee_percent'] ?? 10) ?>%</span></div>
            <div class="rule"><span>VIP Level</span><span>VIP <?= $user['vip_level'] ?></span></div>
        </div>
    </div>

    <?php if (empty($banks)): ?>
    <div class="alert alert-info">
        Anda belum memiliki rekening bank. <a href="<?= BASE_URL ?>/pages/bank_account.php">Tambah rekening</a> terlebih dahulu.
    </div>
    <?php endif; ?>

    <form id="wd-form" <?= ($blocked || empty($banks)) ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
        <div class="form-group">
            <label>Pilih Rekening</label>
            <select id="bank-select" class="form-input" required>
                <option value="">-- Pilih Rekening --</option>
                <?php foreach ($banks as $b): ?>
                <option value="<?= $b['id'] ?>"><?= sanitize($b['bank_name']) ?> - <?= sanitize($b['account_number']) ?> (<?= sanitize($b['account_holder']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jumlah Penarikan</label>
            <input type="number" id="wd-amount" class="form-input" placeholder="Masukkan jumlah" min="<?= $vip['min_withdraw'] ?? 100000 ?>" max="<?= $wallet['balance'] ?>">
            <small class="text-muted">Min <?= format_rupiah($vip['min_withdraw'] ?? 100000) ?></small>
        </div>

        <div class="wd-summary glassmorphism" id="wd-summary" style="display:none;">
            <div class="summary-row"><span>Jumlah</span><span id="sum-amount">-</span></div>
            <div class="summary-row"><span>Fee (<?= $vip['withdraw_fee_percent'] ?? 10 ?>%)</span><span id="sum-fee">-</span></div>
            <div class="summary-row total"><span>Diterima</span><span id="sum-net">-</span></div>
        </div>

        <button type="button" class="btn btn-primary btn-full" onclick="submitWd()" <?= $blocked ? 'disabled' : '' ?>>Ajukan Penarikan</button>
    </form>
</section>

<?php
$extra_js = '
<script>
const feePercent = ' . ($vip['withdraw_fee_percent'] ?? 10) . ';
document.getElementById("wd-amount").addEventListener("input", (e) => {
    const amt = parseInt(e.target.value) || 0;
    if (amt > 0) {
        const fee = Math.round(amt * feePercent / 100);
        const net = amt - fee;
        document.getElementById("sum-amount").textContent = formatRupiah(amt);
        document.getElementById("sum-fee").textContent = "-" + formatRupiah(fee);
        document.getElementById("sum-net").textContent = formatRupiah(net);
        document.getElementById("wd-summary").style.display = "block";
    } else {
        document.getElementById("wd-summary").style.display = "none";
    }
});

async function submitWd() {
    const bank_id = document.getElementById("bank-select").value;
    const amount = parseInt(document.getElementById("wd-amount").value) || 0;
    if (!bank_id) { showToast("Pilih rekening terlebih dahulu.", "error"); return; }
    if (amount < ' . ($vip['min_withdraw'] ?? 100000) . ') { showToast("Jumlah di bawah minimum.", "error"); return; }
    
    const res = await fetch("' . BASE_URL . '/api/wallet.php?action=withdraw", {
        method:"POST", credentials:"same-origin",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({bank_account_id: parseInt(bank_id), amount})
    });
    const data = await res.json();
    if (data.success) {
        showToast("Pengajuan withdraw berhasil! Menunggu proses admin.", "success");
        setTimeout(() => location.href = "' . BASE_URL . '/pages/history.php", 2000);
    } else {
        showToast(data.message, "error");
    }
}
function formatRupiah(n) { return "Rp " + Math.round(n).toLocaleString("id-ID"); }
</script>';
include INCLUDES_PATH . '/footer.php';
?>

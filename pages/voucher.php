<?php
/**
 * NOXARA - Redeem Voucher
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Redeem Voucher';
$show_back = true;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Redeem Voucher</h2>
    </div>

    <div class="glassmorphism voucher-card">
        <p class="voucher-desc">Masukkan kode voucher untuk menukarkan saldo.</p>
        <div class="form-group">
            <label class="form-label">Kode Voucher</label>
            <input type="text" id="voucherCode" class="form-input"
                   placeholder="Masukkan kode voucher" maxlength="32" autocomplete="off">
        </div>
        <button onclick="redeemVoucher()" class="btn btn-primary btn-block" id="btnRedeem">
            Klaim Voucher
        </button>
        <div id="voucherResult" class="voucher-result" style="display:none"></div>
    </div>
</section>


<?php
$extra_js = '
<script>
async function redeemVoucher() {
    const code = document.getElementById("voucherCode").value.trim();
    const btn = document.getElementById("btnRedeem");
    const result = document.getElementById("voucherResult");
    if (!code) { showToast("Masukkan kode voucher.", "error"); return; }
    btn.disabled = true;
    btn.textContent = "Memproses...";
    try {
        const res = await fetch("' . BASE_URL . '/api/wallet.php?action=redeem_voucher", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({code: code})
        });
        const data = await res.json();
        result.style.display = "block";
        if (data.success) {
            result.className = "voucher-result success";
            result.textContent = "Berhasil! Saldo +" + data.amount_formatted;
            showToast("Voucher berhasil diklaim!", "success");
            document.getElementById("voucherCode").value = "";
        } else {
            result.className = "voucher-result error";
            result.textContent = data.message;
            showToast(data.message, "error");
        }
    } catch(e) {
        showToast("Gagal. Coba lagi.", "error");
    }
    btn.disabled = false;
    btn.textContent = "Klaim Voucher";
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

<?php
/**
 * NOXARA - Topup Page
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Isi Saldo';
$show_back = true;

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <h2 class="section-title">Isi Saldo via QRIS</h2>
    <p class="text-muted">Pilih nominal atau masukkan jumlah custom. Bayar menggunakan aplikasi e-wallet atau mobile banking manapun yang mendukung QRIS.</p>

    <div class="topup-presets" id="topup-presets">
        <button class="preset-btn" data-amount="50000">Rp 50.000</button>
        <button class="preset-btn" data-amount="100000">Rp 100.000</button>
        <button class="preset-btn" data-amount="200000">Rp 200.000</button>
        <button class="preset-btn" data-amount="500000">Rp 500.000</button>
        <button class="preset-btn" data-amount="1000000">Rp 1.000.000</button>
        <button class="preset-btn" data-amount="2000000">Rp 2.000.000</button>
        <button class="preset-btn" data-amount="5000000">Rp 5.000.000</button>
        <button class="preset-btn active" data-amount="custom">Custom</button>
    </div>

    <div class="form-group">
        <label>Nominal Top Up</label>
        <input type="number" id="topup-amount" class="form-input" placeholder="Min Rp 10.000" min="10000" max="50000000" value="100000">
        <small class="text-muted">Min Rp 10.000 - Max Rp 50.000.000</small>
    </div>

    <div class="form-group">
        <label>Kode Voucher (opsional)</label>
        <div class="input-group">
            <input type="text" id="voucher-code" class="form-input" placeholder="Masukkan kode voucher">
            <button class="btn btn-sm btn-outline" onclick="validateVoucher()">Cek</button>
        </div>
        <div id="voucher-result"></div>
    </div>

    <div class="topup-summary glassmorphism" id="topup-summary">
        <div class="summary-row"><span>Nominal</span><span id="sum-nominal">Rp 100.000</span></div>
        <div class="summary-row" id="sum-bonus-row" style="display:none;"><span>Bonus Voucher</span><span id="sum-bonus" class="text-success">+Rp 0</span></div>
        <div class="summary-row"><span>Biaya</span><span class="text-success">GRATIS</span></div>
    </div>

    <div class="payment-logos">
        <span class="text-muted">Didukung oleh:</span>
        <div class="logo-grid">
            <span class="bank-logo">BCA</span><span class="bank-logo">BRI</span><span class="bank-logo">BNI</span>
            <span class="bank-logo">Mandiri</span><span class="bank-logo">DANA</span><span class="bank-logo">OVO</span>
            <span class="bank-logo">GoPay</span><span class="bank-logo">ShopeePay</span>
        </div>
    </div>

    <button class="btn btn-primary btn-full pulse-glow" id="btn-topup" onclick="processTopup()">
        Lanjut Bayar
    </button>
</section>

<?php
$extra_js = '
<script>
let selectedAmount = 100000;
let voucherId = null;
let voucherBonus = 0;

document.querySelectorAll(".preset-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".preset-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        const amt = btn.dataset.amount;
        if (amt !== "custom") {
            selectedAmount = parseInt(amt);
            document.getElementById("topup-amount").value = amt;
        }
        updateSummary();
    });
});

document.getElementById("topup-amount").addEventListener("input", (e) => {
    selectedAmount = parseInt(e.target.value) || 0;
    updateSummary();
});

function updateSummary() {
    document.getElementById("sum-nominal").textContent = formatRupiah(selectedAmount);
    if (voucherBonus > 0) {
        document.getElementById("sum-bonus-row").style.display = "flex";
        document.getElementById("sum-bonus").textContent = "+" + formatRupiah(voucherBonus);
    } else {
        document.getElementById("sum-bonus-row").style.display = "none";
    }
}

async function validateVoucher() {
    const code = document.getElementById("voucher-code").value.trim();
    if (!code) return;
    const res = await fetch("' . BASE_URL . '/api/topup.php?action=validate_voucher", {
        method:"POST", credentials:"same-origin",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({code, amount: selectedAmount})
    });
    const data = await res.json();
    if (data.valid) {
        voucherId = data.voucher_id;
        voucherBonus = data.bonus;
        document.getElementById("voucher-result").innerHTML = "<span class=\"text-success\">Bonus +" + formatRupiah(data.bonus) + " berlaku!</span>";
    } else {
        voucherId = null; voucherBonus = 0;
        document.getElementById("voucher-result").innerHTML = "<span class=\"text-error\">" + data.message + "</span>";
    }
    updateSummary();
}

async function processTopup() {
    if (selectedAmount < 10000) { showToast("Minimal Rp 10.000", "error"); return; }
    if (selectedAmount > 50000000) { showToast("Maksimal Rp 50.000.000", "error"); return; }
    
    document.getElementById("btn-topup").disabled = true;
    document.getElementById("btn-topup").textContent = "Memproses...";
    
    const res = await fetch("' . BASE_URL . '/api/topup.php?action=create", {
        method:"POST", credentials:"same-origin",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({amount: selectedAmount, voucher_id: voucherId})
    });
    const data = await res.json();
    if (data.success) {
        location.href = "' . BASE_URL . '/pages/topup_pay.php?id=" + data.topup_id;
    } else {
        showToast(data.message, "error");
        document.getElementById("btn-topup").disabled = false;
        document.getElementById("btn-topup").textContent = "Lanjut Bayar";
    }
}

function formatRupiah(n) { return "Rp " + Math.round(n).toLocaleString("id-ID"); }
updateSummary();
</script>';
include INCLUDES_PATH . '/footer.php';
?>

<?php
/**
 * NOXARA - Products Page
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Produk Mining';
$show_back = true;
$current_page = 'products';

$categories = db()->query("SELECT * FROM product_categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$products = db()->query("SELECT p.*, pc.name as cat_name, pc.slug as cat_slug FROM products p JOIN product_categories pc ON pc.id = p.category_id WHERE p.is_active = 1 ORDER BY p.sort_order")->fetch_all(MYSQLI_ASSOC);

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <!-- Category Tabs -->
    <div class="tab-bar scroll-x">
        <button class="tab-item active" data-cat="all">Semua</button>
        <?php foreach ($categories as $cat): ?>
        <button class="tab-item" data-cat="<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="products-grid" id="products-grid">
        <?php foreach ($products as $p): ?>
        <div class="product-card glassmorphism scroll-reveal" data-cat="<?= $p['cat_slug'] ?>">
            <div class="product-badge cat-<?= $p['cat_slug'] ?>"><?= sanitize($p['cat_name']) ?></div>
            <?php if ($p['min_vip'] > 0): ?>
            <div class="product-vip-lock">VIP <?= $p['min_vip'] ?>+</div>
            <?php endif; ?>
            <h3 class="product-name"><?= sanitize($p['name']) ?></h3>
            <div class="product-price"><?= format_rupiah($p['price']) ?></div>
            <div class="product-stats">
                <div class="stat"><span class="label">Profit/hari</span><span class="value text-cyan"><?= format_rupiah($p['daily_profit']) ?></span></div>
                <div class="stat"><span class="label">Durasi</span><span class="value"><?= $p['duration_days'] ?> hari</span></div>
                <div class="stat"><span class="label">Total Profit</span><span class="value text-gold"><?= format_rupiah($p['total_profit']) ?></span></div>
                <div class="stat"><span class="label">ROI</span><span class="value text-purple"><?= $p['roi_percent'] ?>%</span></div>
            </div>
            <button class="btn btn-primary btn-full" onclick="showBuyPopup(<?= $p['id'] ?>, '<?= sanitize($p['name']) ?>', <?= $p['price'] ?>, <?= $p['daily_profit'] ?>, <?= $p['duration_days'] ?>, <?= $p['total_profit'] ?>, <?= $p['roi_percent'] ?>, <?= $p['min_vip'] ?>)">
                Beli Paket
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Buy Popup -->
<div id="buy-popup" class="modal-overlay" style="display:none;">
    <div class="modal-content glassmorphism">
        <div class="modal-header">
            <h3 id="popup-title">Detail Paket</h3>
            <button class="btn-icon" onclick="closeBuyPopup()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="popup-detail" id="popup-detail"></div>
            <div class="form-group">
                <label>Kode Voucher (opsional)</label>
                <div class="input-group">
                    <input type="text" id="voucher-code" class="form-input" placeholder="Masukkan kode">
                    <button class="btn btn-sm btn-outline" onclick="checkVoucher()">Cek</button>
                </div>
                <div id="voucher-result" class="voucher-result"></div>
            </div>
            <div class="popup-summary" id="popup-summary"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary btn-full pulse-glow" id="btn-confirm-buy" onclick="confirmBuy()">Konfirmasi Beli</button>
        </div>
    </div>
</div>

<!-- PIN Popup -->
<div id="pin-popup" class="modal-overlay" style="display:none;">
    <div class="modal-content glassmorphism modal-sm">
        <h3 class="text-center">Masukkan PIN</h3>
        <p class="text-center text-muted">PIN 6 digit untuk konfirmasi</p>
        <div class="pin-input-group" id="pin-inputs">
            <input type="password" maxlength="1" class="pin-box" data-index="0" inputmode="numeric">
            <input type="password" maxlength="1" class="pin-box" data-index="1" inputmode="numeric">
            <input type="password" maxlength="1" class="pin-box" data-index="2" inputmode="numeric">
            <input type="password" maxlength="1" class="pin-box" data-index="3" inputmode="numeric">
            <input type="password" maxlength="1" class="pin-box" data-index="4" inputmode="numeric">
            <input type="password" maxlength="1" class="pin-box" data-index="5" inputmode="numeric">
        </div>
        <div id="pin-error" class="text-error text-center" style="display:none;"></div>
        <button class="btn btn-primary btn-full" id="btn-submit-pin" onclick="submitPin()" style="margin-top:16px;">Konfirmasi</button>
        <button class="btn btn-ghost btn-full" onclick="closePinPopup()">Batal</button>
    </div>
</div>

<?php
$extra_js = '
<script>
let selectedProduct = null;
let voucherDiscount = 0;
let voucherId = null;

function showBuyPopup(id, name, price, daily, days, total, roi, minVip) {
    const userVip = ' . ($user['vip_level'] ?? 0) . ';
    if (minVip > userVip) {
        showToast("Paket ini hanya untuk VIP " + minVip + " ke atas.", "error");
        return;
    }
    selectedProduct = {id, name, price, daily, days, total, roi};
    voucherDiscount = 0; voucherId = null;
    document.getElementById("popup-title").textContent = name;
    document.getElementById("popup-detail").innerHTML = `
        <div class="detail-row"><span>Harga</span><span>${formatRupiah(price)}</span></div>
        <div class="detail-row"><span>Profit/hari</span><span class="text-cyan">${formatRupiah(daily)}</span></div>
        <div class="detail-row"><span>Durasi</span><span>${days} hari</span></div>
        <div class="detail-row"><span>Total Profit</span><span class="text-gold">${formatRupiah(total)}</span></div>
        <div class="detail-row"><span>ROI</span><span class="text-purple">${roi}%</span></div>
    `;
    updateSummary();
    document.getElementById("buy-popup").style.display = "flex";
    document.getElementById("voucher-code").value = "";
    document.getElementById("voucher-result").innerHTML = "";
}

function closeBuyPopup() { document.getElementById("buy-popup").style.display = "none"; }

function updateSummary() {
    const final = selectedProduct.price - voucherDiscount;
    document.getElementById("popup-summary").innerHTML = `
        <div class="summary-row"><span>Subtotal</span><span>${formatRupiah(selectedProduct.price)}</span></div>
        ${voucherDiscount > 0 ? "<div class=\"summary-row text-success\"><span>Diskon Voucher</span><span>-" + formatRupiah(voucherDiscount) + "</span></div>" : ""}
        <div class="summary-row total"><span>Total Bayar</span><span>${formatRupiah(final)}</span></div>
    `;
}

async function checkVoucher() {
    const code = document.getElementById("voucher-code").value.trim();
    if (!code) return;
    const res = await fetch("' . BASE_URL . '/api/wallet.php?action=validate_voucher", {
        method: "POST", credentials: "same-origin",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({code, type:"product_discount", amount: selectedProduct.price, product_id: selectedProduct.id})
    });
    const data = await res.json();
    if (data.valid) {
        voucherDiscount = data.discount;
        voucherId = data.voucher_id;
        document.getElementById("voucher-result").innerHTML = "<span class=\"text-success\">Diskon " + formatRupiah(data.discount) + " berlaku!</span>";
    } else {
        voucherDiscount = 0; voucherId = null;
        document.getElementById("voucher-result").innerHTML = "<span class=\"text-error\">" + data.message + "</span>";
    }
    updateSummary();
}

function confirmBuy() {
    closeBuyPopup();
    document.getElementById("pin-popup").style.display = "flex";
    document.querySelectorAll(".pin-box").forEach(b => b.value = "");
    document.querySelector(".pin-box").focus();
}

function closePinPopup() { document.getElementById("pin-popup").style.display = "none"; }

async function submitPin() {
    const pins = [...document.querySelectorAll(".pin-box")].map(b => b.value).join("");
    if (pins.length < 6) { document.getElementById("pin-error").textContent = "PIN harus 6 digit"; document.getElementById("pin-error").style.display = "block"; return; }
    
    const res = await fetch("' . BASE_URL . '/api/mining.php?action=purchase", {
        method: "POST", credentials: "same-origin",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({product_id: selectedProduct.id, pin: pins, voucher_id: voucherId})
    });
    const data = await res.json();
    if (data.success) {
        closePinPopup();
        showToast("Pembelian berhasil!", "success");
        triggerConfetti();
        setTimeout(() => location.href = "' . BASE_URL . '/pages/my_packages.php", 2000);
    } else {
        document.getElementById("pin-error").textContent = data.message;
        document.getElementById("pin-error").style.display = "block";
    }
}

// PIN auto-focus
document.querySelectorAll(".pin-box").forEach((box, i, all) => {
    box.addEventListener("input", () => { if (box.value && i < 5) all[i+1].focus(); });
    box.addEventListener("keydown", (e) => { if (e.key === "Backspace" && !box.value && i > 0) all[i-1].focus(); });
});

// Tab filter
document.querySelectorAll(".tab-item").forEach(tab => {
    tab.addEventListener("click", () => {
        document.querySelectorAll(".tab-item").forEach(t => t.classList.remove("active"));
        tab.classList.add("active");
        const cat = tab.dataset.cat;
        document.querySelectorAll(".product-card").forEach(c => {
            c.style.display = (cat === "all" || c.dataset.cat === cat) ? "" : "none";
        });
    });
});

function formatRupiah(n) { return "Rp " + Math.round(n).toLocaleString("id-ID"); }
</script>';
include INCLUDES_PATH . '/footer.php';
?>

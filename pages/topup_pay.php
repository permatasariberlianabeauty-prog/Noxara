<?php
/**
 * NOXARA - Topup Payment (QR + Polling)
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Pembayaran';
$show_back = true;

$topup_id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM topups WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param('ii', $topup_id, $user['id']);
$stmt->execute();
$topup = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$topup) {
    redirect(BASE_URL . '/pages/topup.php');
}

$qr_image_url = 'https://larabert-qrgen.hf.space/v1/create-qr-code?size=500x500&style=2&color=00D4FF&data=' . urlencode($topup['qr_string']);

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section topup-pay-section">
    <div class="qr-card glassmorphism">
        <div class="qr-header gradient-bg">
            <span class="orbitron text-white">NOXARA</span>
            <span class="text-sm text-white">Pembayaran QRIS</span>
        </div>
        <div class="qr-body">
            <img src="<?= $qr_image_url ?>" alt="QR Code" class="qr-image" id="qr-image">
            <div class="qr-amount">
                <span class="label">Total Bayar</span>
                <span class="amount"><?= format_rupiah($topup['total_amount']) ?></span>
            </div>
            <div class="qr-countdown">
                <span class="label">Berlaku hingga</span>
                <span class="countdown" id="qr-countdown" data-expires="<?= $topup['expires_at'] ?>">15:00</span>
            </div>
        </div>
        <div class="qr-actions">
            <button class="btn btn-outline btn-sm" onclick="copyQR()">Salin Kode QR</button>
            <button class="btn btn-danger btn-sm" onclick="cancelTopup()">Batalkan</button>
        </div>
    </div>

    <div class="pay-status glassmorphism" id="pay-status">
        <div class="status-icon pending">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span>Menunggu pembayaran...</span>
    </div>

    <input type="hidden" id="qr-string" value="<?= htmlspecialchars($topup['qr_string']) ?>">
    <input type="hidden" id="topup-id" value="<?= $topup_id ?>">
</section>

<?php
$extra_js = '
<script src="' . BASE_URL . '/assets/js/countdown.js"></script>
<script>
const topupId = document.getElementById("topup-id").value;
let pollInterval;

// Start polling every 5 seconds
pollInterval = setInterval(checkStatus, 5000);

async function checkStatus() {
    try {
        const res = await fetch("' . BASE_URL . '/api/topup.php?action=status&id=" + topupId, {credentials:"same-origin"});
        const data = await res.json();
        if (data.status === "paid") {
            clearInterval(pollInterval);
            document.getElementById("pay-status").innerHTML = `
                <div class="status-icon success"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                <span class="text-success">Pembayaran berhasil!</span>
            `;
            showToast("Top up berhasil! Saldo telah ditambahkan.", "success");
            triggerConfetti();
            setTimeout(() => location.href = "' . BASE_URL . '/pages/dashboard.php", 3000);
        } else if (data.status === "expired" || data.status === "cancelled") {
            clearInterval(pollInterval);
            document.getElementById("pay-status").innerHTML = `
                <div class="status-icon error"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
                <span class="text-error">Pembayaran ${data.status === "expired" ? "expired" : "dibatalkan"}.</span>
            `;
        }
    } catch(e) {}
}

function copyQR() {
    const qr = document.getElementById("qr-string").value;
    navigator.clipboard.writeText(qr).then(() => showToast("Kode QR disalin!", "success"));
}

async function cancelTopup() {
    if (!confirm("Batalkan pembayaran ini?")) return;
    const res = await fetch("' . BASE_URL . '/api/topup.php?action=cancel", {
        method:"POST", credentials:"same-origin",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({topup_id: parseInt(topupId)})
    });
    const data = await res.json();
    showToast(data.message || "Dibatalkan", data.success ? "success" : "error");
    if (data.success) setTimeout(() => location.href = "' . BASE_URL . '/pages/topup.php", 1500);
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

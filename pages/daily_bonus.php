<?php
/**
 * NOXARA - Daily Bonus Center
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Daily Bonus';
$show_back = true;

// Check-in streak
$stmt = db()->prepare("SELECT streak, last_checkin FROM daily_checkins WHERE user_id = ? LIMIT 1");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$checkin = $stmt->get_result()->fetch_assoc();
$stmt->close();

$streak = $checkin['streak'] ?? 0;
$last_checkin = $checkin['last_checkin'] ?? null;
$checked_today = ($last_checkin === date('Y-m-d'));

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Daily Bonus Center</h2>
    </div>

    <!-- Check-in Strip -->
    <div class="glassmorphism checkin-card">
        <h4>Daily Check-in</h4>
        <p>Streak: <strong><?= $streak ?> hari</strong></p>
        <div class="checkin-strip">
            <?php for ($i = 1; $i <= 7; $i++): ?>
            <div class="checkin-day <?= $i <= $streak % 7 ? 'checked' : '' ?>
                <?= $i === (($streak % 7) + 1) && !$checked_today ? 'current' : '' ?>">
                <span class="day-num"><?= $i ?></span>
            </div>
            <?php endfor; ?>
        </div>

        <?php if (!$checked_today): ?>
        <button onclick="doCheckin()" class="btn btn-primary btn-block" id="btnCheckin">Check-in Hari Ini</button>
        <?php else: ?>
        <button class="btn btn-secondary btn-block" disabled>Sudah Check-in Hari Ini</button>
        <?php endif; ?>
    </div>

    <!-- Spin Wheel -->
    <div class="glassmorphism bonus-card">
        <div class="bonus-card-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 010 20"/><path d="M12 12l5-3"/></svg>
        </div>
        <h4>Spin Wheel</h4>
        <p>Putar roda keberuntungan dan dapatkan bonus acak!</p>
        <button onclick="spinWheel()" class="btn btn-primary">Putar Sekarang</button>
    </div>

    <!-- Daily Quests -->
    <div class="glassmorphism bonus-card">
        <div class="bonus-card-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        </div>
        <h4>Daily Quests</h4>
        <p>Selesaikan quest harian untuk mendapatkan bonus tambahan.</p>
        <ul class="quest-list">
            <li class="quest-item">Login hari ini <span class="badge badge-success">Done</span></li>
            <li class="quest-item">Mining 1x <span class="badge badge-secondary">Belum</span></li>
            <li class="quest-item">Share referral <span class="badge badge-secondary">Belum</span></li>
        </ul>
    </div>

    <!-- Mystery Box -->
    <div class="glassmorphism bonus-card">
        <div class="bonus-card-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#A78BFA" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <h4>Mystery Box</h4>
        <p>Buka mystery box untuk hadiah kejutan!</p>
        <button onclick="openMysteryBox()" class="btn btn-primary">Buka Box</button>
    </div>
</section>


<?php
$extra_js = '
<script>
async function doCheckin() {
    const btn = document.getElementById("btnCheckin");
    btn.disabled = true;
    try {
        const res = await fetch("' . BASE_URL . '/api/wallet.php?action=daily_checkin", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"}
        });
        const data = await res.json();
        if (data.success) {
            showToast("Check-in berhasil! +" + data.bonus_formatted, "success");
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, "error");
            btn.disabled = false;
        }
    } catch(e) { showToast("Gagal.", "error"); btn.disabled = false; }
}
async function spinWheel() {
    try {
        const res = await fetch("' . BASE_URL . '/api/wallet.php?action=spin_wheel", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"}
        });
        const data = await res.json();
        if (data.success) {
            showToast("Selamat! Kamu dapat " + data.prize_formatted, "success");
        } else { showToast(data.message, "error"); }
    } catch(e) { showToast("Gagal.", "error"); }
}
async function openMysteryBox() {
    try {
        const res = await fetch("' . BASE_URL . '/api/wallet.php?action=mystery_box", {
            method: "POST", credentials: "same-origin",
            headers: {"Content-Type":"application/json"}
        });
        const data = await res.json();
        if (data.success) {
            showToast("Mystery Box: " + data.reward_formatted, "success");
        } else { showToast(data.message, "error"); }
    } catch(e) { showToast("Gagal.", "error"); }
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

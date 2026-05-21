<?php
/**
 * NOXARA - Referral Center
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Referral';
$show_back = true;

$referral_code = $user['referral_code'];
$referral_link = BASE_URL . '/auth/register.php?ref=' . $referral_code;

// Get downlines L1
$stmt_l1 = db()->prepare("SELECT u.id, u.username, u.created_at, u.is_active FROM users u WHERE u.referred_by = ? ORDER BY u.created_at DESC");
$stmt_l1->bind_param('i', $user['id']);
$stmt_l1->execute();
$downlines_l1 = $stmt_l1->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_l1->close();

// Get downlines L2
$l1_ids = array_column($downlines_l1, 'id');
$downlines_l2 = [];
if (!empty($l1_ids)) {
    $placeholders = implode(',', array_fill(0, count($l1_ids), '?'));
    $types = str_repeat('i', count($l1_ids));
    $stmt_l2 = db()->prepare("SELECT u.id, u.username, u.created_at, u.is_active FROM users u WHERE u.referred_by IN ($placeholders) ORDER BY u.created_at DESC");
    $stmt_l2->bind_param($types, ...$l1_ids);
    $stmt_l2->execute();
    $downlines_l2 = $stmt_l2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_l2->close();
}

// Get downlines L3
$l2_ids = array_column($downlines_l2, 'id');
$downlines_l3 = [];
if (!empty($l2_ids)) {
    $placeholders = implode(',', array_fill(0, count($l2_ids), '?'));
    $types = str_repeat('i', count($l2_ids));
    $stmt_l3 = db()->prepare("SELECT u.id, u.username, u.created_at, u.is_active FROM users u WHERE u.referred_by IN ($placeholders) ORDER BY u.created_at DESC");
    $stmt_l3->bind_param($types, ...$l2_ids);
    $stmt_l3->execute();
    $downlines_l3 = $stmt_l3->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_l3->close();
}

// Total earned from referral commissions
$stmt_earn = db()->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'referral'");
$stmt_earn->bind_param('i', $user['id']);
$stmt_earn->execute();
$total_earned = $stmt_earn->get_result()->fetch_assoc()['total'];
$stmt_earn->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Referral Center</h2>
    </div>

    <!-- Referral Code Card -->
    <div class="glassmorphism referral-code-card">
        <div class="referral-label">Kode Referral Kamu</div>
        <div class="referral-code-display">
            <span class="referral-code" id="refCode"><?= sanitize($referral_code) ?></span>
        </div>
        <div class="referral-link-box">
            <input type="text" value="<?= sanitize($referral_link) ?>" id="refLink" readonly class="input-referral">
        </div>
        <div class="referral-share-buttons">
            <a href="https://wa.me/?text=Gabung%20NOXARA%20dan%20mulai%20mining!%20Gunakan%20kode%20<?= $referral_code ?>%20<?= urlencode($referral_link) ?>" target="_blank" class="btn btn-share btn-whatsapp">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.634-1.215A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-2.115 0-4.142-.57-5.913-1.652l-.424-.253-2.75.721.735-2.686-.278-.44A9.72 9.72 0 012.25 12C2.25 6.624 6.624 2.25 12 2.25S21.75 6.624 21.75 12 17.376 21.75 12 21.75z"/></svg>
                WhatsApp
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode($referral_link) ?>&text=Gabung%20NOXARA%20mining!" target="_blank" class="btn btn-share btn-telegram">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.492-1.302.48-.428-.012-1.252-.242-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Telegram
            </a>
            <button onclick="copyReferral()" class="btn btn-share btn-copy">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Copy
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="glassmorphism referral-stats">
        <div class="stat-grid">
            <div class="stat-item">
                <span class="stat-value"><?= count($downlines_l1) ?></span>
                <span class="stat-label">Level 1</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= count($downlines_l2) ?></span>
                <span class="stat-label">Level 2</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= count($downlines_l3) ?></span>
                <span class="stat-label">Level 3</span>
            </div>
            <div class="stat-item">
                <span class="stat-value text-success"><?= format_rupiah($total_earned) ?></span>
                <span class="stat-label">Total Komisi</span>
            </div>
        </div>
    </div>

    <!-- Downline Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="showTab('l1')">L1 (<?= count($downlines_l1) ?>)</button>
        <button class="filter-tab" onclick="showTab('l2')">L2 (<?= count($downlines_l2) ?>)</button>
        <button class="filter-tab" onclick="showTab('l3')">L3 (<?= count($downlines_l3) ?>)</button>
    </div>

    <!-- L1 List -->
    <div class="downline-tab" id="tab-l1">
        <?php if (empty($downlines_l1)): ?>
        <div class="empty-state"><p>Belum ada downline Level 1.</p></div>
        <?php else: ?>
        <?php foreach ($downlines_l1 as $dl): ?>
        <div class="downline-item glassmorphism">
            <div class="downline-info">
                <span class="downline-name"><?= sanitize($dl['username']) ?></span>
                <span class="downline-date"><?= time_ago($dl['created_at']) ?></span>
            </div>
            <span class="badge badge-<?= $dl['is_active'] ? 'success' : 'secondary' ?>">
                <?= $dl['is_active'] ? 'ON' : 'OFF' ?>
            </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- L2 List -->
    <div class="downline-tab" id="tab-l2" style="display:none">
        <?php if (empty($downlines_l2)): ?>
        <div class="empty-state"><p>Belum ada downline Level 2.</p></div>
        <?php else: ?>
        <?php foreach ($downlines_l2 as $dl): ?>
        <div class="downline-item glassmorphism">
            <div class="downline-info">
                <span class="downline-name"><?= sanitize($dl['username']) ?></span>
                <span class="downline-date"><?= time_ago($dl['created_at']) ?></span>
            </div>
            <span class="badge badge-<?= $dl['is_active'] ? 'success' : 'secondary' ?>">
                <?= $dl['is_active'] ? 'ON' : 'OFF' ?>
            </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- L3 List -->
    <div class="downline-tab" id="tab-l3" style="display:none">
        <?php if (empty($downlines_l3)): ?>
        <div class="empty-state"><p>Belum ada downline Level 3.</p></div>
        <?php else: ?>
        <?php foreach ($downlines_l3 as $dl): ?>
        <div class="downline-item glassmorphism">
            <div class="downline-info">
                <span class="downline-name"><?= sanitize($dl['username']) ?></span>
                <span class="downline-date"><?= time_ago($dl['created_at']) ?></span>
            </div>
            <span class="badge badge-<?= $dl['is_active'] ? 'success' : 'secondary' ?>">
                <?= $dl['is_active'] ? 'ON' : 'OFF' ?>
            </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php
$extra_js = '
<script>
function showTab(level) {
    document.querySelectorAll(".downline-tab").forEach(t => t.style.display = "none");
    document.querySelectorAll(".filter-tab").forEach(t => t.classList.remove("active"));
    document.getElementById("tab-" + level).style.display = "block";
    event.target.classList.add("active");
}
function copyReferral() {
    const link = document.getElementById("refLink").value;
    navigator.clipboard.writeText(link).then(() => {
        showToast("Link referral disalin!", "success");
    });
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>

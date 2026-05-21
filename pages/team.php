<?php
/**
 * NOXARA - Team Members
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Tim Kami';
$show_back = true;

// Get team members
$members = db()->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Tim NOXARA</h2>
    </div>

    <?php if (empty($members)): ?>
    <div class="empty-state"><p>Data tim belum tersedia.</p></div>
    <?php else: ?>
    <div class="team-grid">
        <?php foreach ($members as $member): ?>
        <div class="team-card glassmorphism">
            <div class="team-avatar">
                <?php if (!empty($member['photo'])): ?>
                <img src="<?= BASE_URL ?>/uploads/team/<?= sanitize($member['photo']) ?>" alt="<?= sanitize($member['name']) ?>">
                <?php else: ?>
                <div class="avatar-placeholder"><?= strtoupper(substr($member['name'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="team-info">
                <h4><?= sanitize($member['name']) ?></h4>
                <span class="team-role"><?= sanitize($member['role'] ?? '') ?></span>
                <?php if (!empty($member['bio'])): ?>
                <p class="team-bio"><?= sanitize($member['bio']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

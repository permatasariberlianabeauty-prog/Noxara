<?php
/**
 * NOXARA - Notifications
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Notifikasi';
$show_back = true;

// Mark all as read on load
$stmt_mark = db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt_mark->bind_param('i', $user['id']);
$stmt_mark->execute();
$stmt_mark->close();

// Get notifications
$stmt = db()->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Notifikasi</h2>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="empty-state">
        <img src="<?= BASE_URL ?>/assets/img/illustrations/empty-state.svg" alt="Kosong" width="120">
        <p>Belum ada notifikasi.</p>
    </div>
    <?php else: ?>
    <div class="notification-list">
        <?php foreach ($notifications as $notif): ?>
        <div class="notification-item glassmorphism <?= $notif['is_read'] ? '' : 'unread' ?>">
            <div class="notif-icon">
                <?php if (($notif['type'] ?? '') === 'success'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?php elseif (($notif['type'] ?? '') === 'warning'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php else: ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#A78BFA" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <?php endif; ?>
            </div>
            <div class="notif-content">
                <span class="notif-title"><?= sanitize($notif['title'] ?? 'Notifikasi') ?></span>
                <span class="notif-message"><?= sanitize($notif['message'] ?? '') ?></span>
                <span class="notif-time"><?= time_ago($notif['created_at']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<?php
/**
 * NOXARA - Legal/Info Pages
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$show_back = true;

$slug = $_GET['slug'] ?? '';
if (!$slug || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
    redirect(BASE_URL . '/pages/dashboard.php');
}

// Get page from legal_pages table
$stmt = db()->prepare("SELECT * FROM legal_pages WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$page) {
    redirect(BASE_URL . '/pages/dashboard.php');
}

$page_title = $page['title'];

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title"><?= sanitize($page['title']) ?></h2>
    </div>

    <div class="glassmorphism legal-content">
        <div class="legal-body">
            <?= $page['content'] ?>
        </div>
        <?php if (!empty($page['updated_at'])): ?>
        <div class="legal-footer">
            <p class="text-muted">Terakhir diperbarui: <?= date_id($page['updated_at']) ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

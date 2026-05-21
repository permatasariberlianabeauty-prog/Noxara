<?php
/**
 * NOXARA - Admin Layout
 */
if (!defined('ADMIN_PAGE')) { http_response_code(403); exit; }

// Check admin session
if (empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$admin = null;
$stmt = db()->prepare("SELECT * FROM admin_users WHERE id = ? AND status = 'active'");
$stmt->bind_param('i', $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
    unset($_SESSION['admin_id']);
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$admin_page_title = $admin_page_title ?? 'Admin Panel';
$current_admin_page = $current_admin_page ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_page_title) ?> | NOXARA Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#0A0E1A; color:#e0e0e0; min-height:100vh; display:flex; }
        .admin-sidebar { width:250px; background:#0F1629; border-right:1px solid #1a2340; height:100vh; position:fixed; overflow-y:auto; padding:20px 0; }
        .admin-sidebar .logo { text-align:center; padding:0 20px 20px; border-bottom:1px solid #1a2340; margin-bottom:16px; }
        .admin-sidebar .logo h2 { color:#00D4FF; font-size:18px; }
        .admin-sidebar .logo small { color:#888; }
        .admin-nav a { display:flex; align-items:center; padding:10px 20px; color:#aaa; text-decoration:none; font-size:13px; transition:all .2s; }
        .admin-nav a:hover, .admin-nav a.active { background:#1a2340; color:#00D4FF; border-left:3px solid #00D4FF; }
        .admin-nav .nav-section { padding:10px 20px 5px; font-size:11px; text-transform:uppercase; color:#555; letter-spacing:1px; margin-top:10px; }
        .admin-main { margin-left:250px; flex:1; min-height:100vh; }
        .admin-topbar { background:#0F1629; border-bottom:1px solid #1a2340; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100; }
        .admin-topbar h1 { font-size:18px; color:#fff; }
        .admin-topbar .admin-user { color:#888; font-size:13px; }
        .admin-topbar .admin-user a { color:#00D4FF; text-decoration:none; margin-left:10px; }
        .admin-content { padding:30px; }
        .card { background:#0F1629; border:1px solid #1a2340; border-radius:12px; padding:20px; margin-bottom:20px; }
        .card h3 { color:#fff; margin-bottom:15px; font-size:16px; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px; }
        .stat-card { background:linear-gradient(135deg, #0F1629, #1a2340); border:1px solid #1a2340; border-radius:12px; padding:20px; }
        .stat-card .value { font-size:24px; font-weight:700; color:#00D4FF; }
        .stat-card .label { font-size:12px; color:#888; margin-top:4px; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        table th { background:#1a2340; padding:10px 12px; text-align:left; color:#888; font-weight:600; }
        table td { padding:10px 12px; border-bottom:1px solid #1a2340; }
        table tr:hover td { background:#0d1225; }
        .btn-admin { padding:6px 14px; border-radius:6px; border:none; cursor:pointer; font-size:12px; font-weight:600; }
        .btn-admin.primary { background:#00D4FF; color:#0A0E1A; }
        .btn-admin.danger { background:#ff4444; color:#fff; }
        .btn-admin.success { background:#00c853; color:#fff; }
        .badge { padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600; }
        .badge-success { background:rgba(0,200,83,0.2); color:#00c853; }
        .badge-warning { background:rgba(255,193,7,0.2); color:#ffc107; }
        .badge-danger { background:rgba(255,68,68,0.2); color:#ff4444; }
        .badge-info { background:rgba(0,212,255,0.2); color:#00D4FF; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:12px; color:#888; margin-bottom:4px; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:10px; background:#0A0E1A; border:1px solid #1a2340; border-radius:8px; color:#e0e0e0; font-size:14px; }
        .form-group textarea { min-height:100px; resize:vertical; }
        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; }
        .alert-success { background:rgba(0,200,83,0.1); border:1px solid #00c853; color:#00c853; }
        .alert-error { background:rgba(255,68,68,0.1); border:1px solid #ff4444; color:#ff4444; }
        .pagination { display:flex; gap:8px; margin-top:16px; }
        .pagination a { padding:6px 12px; background:#1a2340; color:#aaa; border-radius:6px; text-decoration:none; font-size:12px; }
        .pagination a.active { background:#00D4FF; color:#0A0E1A; }
        @media (max-width: 768px) {
            .admin-sidebar { width:100%; height:auto; position:relative; }
            .admin-main { margin-left:0; }
        }
    </style>
</head>
<body>
<aside class="admin-sidebar">
    <div class="logo">
        <h2>NOXARA</h2>
        <small>Admin Panel</small>
    </div>
    <nav class="admin-nav">
        <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $current_admin_page === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <div class="nav-section">User Management</div>
        <a href="<?= BASE_URL ?>/admin/members.php" class="<?= $current_admin_page === 'members' ? 'active' : '' ?>">Members</a>
        <a href="<?= BASE_URL ?>/admin/withdrawals.php" class="<?= $current_admin_page === 'withdrawals' ? 'active' : '' ?>">Withdrawals</a>
        <a href="<?= BASE_URL ?>/admin/topups.php" class="<?= $current_admin_page === 'topups' ? 'active' : '' ?>">Top Ups</a>
        <a href="<?= BASE_URL ?>/admin/wallet_adjustments.php">Wallet Adjust</a>
        <div class="nav-section">Products & Mining</div>
        <a href="<?= BASE_URL ?>/admin/products.php" class="<?= $current_admin_page === 'products' ? 'active' : '' ?>">Products</a>
        <a href="<?= BASE_URL ?>/admin/vouchers.php" class="<?= $current_admin_page === 'vouchers' ? 'active' : '' ?>">Vouchers</a>
        <a href="<?= BASE_URL ?>/admin/vip_settings.php">VIP Settings</a>
        <a href="<?= BASE_URL ?>/admin/vip_codes.php">VIP Codes</a>
        <a href="<?= BASE_URL ?>/admin/commission_settings.php">Commissions</a>
        <div class="nav-section">Content</div>
        <a href="<?= BASE_URL ?>/admin/banners.php">Banners</a>
        <a href="<?= BASE_URL ?>/admin/popup_settings.php">Popup</a>
        <a href="<?= BASE_URL ?>/admin/marquee_settings.php">Marquee</a>
        <a href="<?= BASE_URL ?>/admin/notifications.php">Notifications</a>
        <a href="<?= BASE_URL ?>/admin/legal_pages.php">Legal Pages</a>
        <div class="nav-section">Bonus & Games</div>
        <a href="<?= BASE_URL ?>/admin/daily_bonus_settings.php">Daily Check-in</a>
        <a href="<?= BASE_URL ?>/admin/spin_wheel.php">Spin Wheel</a>
        <a href="<?= BASE_URL ?>/admin/quest_settings.php">Quests</a>
        <a href="<?= BASE_URL ?>/admin/mystery_box.php">Mystery Box</a>
        <a href="<?= BASE_URL ?>/admin/lucky_draw.php">Lucky Draw</a>
        <a href="<?= BASE_URL ?>/admin/achievements.php">Achievements</a>
        <div class="nav-section">System</div>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="<?= $current_admin_page === 'settings' ? 'active' : '' ?>">Settings</a>
        <a href="<?= BASE_URL ?>/admin/qris_settings.php">QRIS</a>
        <a href="<?= BASE_URL ?>/admin/reports.php">Reports</a>
        <a href="<?= BASE_URL ?>/admin/cron_status.php">Cron Status</a>
        <a href="<?= BASE_URL ?>/admin/backup.php">Backup</a>
        <a href="<?= BASE_URL ?>/admin/admin_security.php">Admin Users</a>
        <a href="<?= BASE_URL ?>/admin/logout.php" style="color:#ff4444;">Logout</a>
    </nav>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <h1><?= htmlspecialchars($admin_page_title) ?></h1>
        <div class="admin-user">
            <?= htmlspecialchars($admin['full_name']) ?> (<?= $admin['role'] ?>)
            <a href="<?= BASE_URL ?>/admin/logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-content">

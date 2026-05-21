<?php
/**
 * NOXARA - Header Component
 */
$page_title = $page_title ?? 'NOXARA';
$show_back = $show_back ?? false;
$user = current_user();
$notif_count = $user ? get_unread_count($user['id']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0A0E1A">
    <meta name="description" content="NOXARA - Invest Smarter, Grow Faster. Platform mining digital #1 Indonesia.">
    <title><?= sanitize($page_title) ?> | NOXARA</title>
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/pwa/icon-192.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/premium.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/mobile.css">
</head>
<body class="theme-dark">
<!-- Splash Screen -->
<div id="splash-screen" class="splash-screen">
    <div class="splash-logo">
        <span class="logo-text gradient-text">N</span>
    </div>
    <div class="splash-loader"></div>
</div>

<!-- Top Bar -->
<header class="top-bar glassmorphism">
    <div class="top-bar-left">
        <?php if ($show_back): ?>
        <a href="javascript:history.back()" class="btn-icon" aria-label="Kembali">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
        <?php else: ?>
        <button id="btn-sidebar" class="btn-icon" aria-label="Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
        <?php endif; ?>
    </div>
    <div class="top-bar-center">
        <span class="logo-brand orbitron"><?= $show_back ? sanitize($page_title) : 'NOXARA' ?></span>
    </div>
    <div class="top-bar-right">
        <a href="<?= BASE_URL ?>/pages/notifications.php" class="btn-icon notif-bell" aria-label="Notifikasi">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <?php if ($notif_count > 0): ?>
            <span class="notif-badge"><?= $notif_count > 99 ? '99+' : $notif_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<main class="main-content">

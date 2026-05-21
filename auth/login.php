<?php
/**
 * NOXARA - Login Page (with Fixed Slider Captcha)
 */
require_once __DIR__ . '/../config/bootstrap.php';

// Already logged in?
if (current_user()) redirect(BASE_URL . '/pages/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid. Muat ulang halaman.';
    } elseif (!captcha_is_verified()) {
        $error = 'Selesaikan verifikasi captcha terlebih dahulu.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            $result = attempt_login($username, $password);
            if ($result['success']) {
                captcha_consume();
                redirect(BASE_URL . '/pages/dashboard.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

require_once INCLUDES_PATH . '/captcha.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0A0E1A">
    <title>Masuk | NOXARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="theme-dark auth-page">
<div class="auth-container">
    <div class="auth-header">
        <h1 class="logo-brand orbitron gradient-text">NOXARA</h1>
        <p class="auth-tagline">Invest Smarter, Grow Faster</p>
    </div>

    <div class="auth-card glassmorphism">
        <h2 class="auth-title">Masuk</h2>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <form method="POST" id="login-form" autocomplete="off">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username/email" required value="<?= sanitize($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required>
                    <button type="button" class="btn-toggle-pw" onclick="togglePassword('password')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <!-- Slider Captcha -->
            <div class="form-group">
                <label>Verifikasi Keamanan</label>
                <div class="captcha-container" id="captcha-container">
                    <div class="captcha-track" id="captcha-track">
                        <div class="captcha-target" id="captcha-target"></div>
                        <div class="captcha-thumb" id="captcha-thumb">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                    </div>
                    <div class="captcha-status" id="captcha-status">
                        <span>Geser untuk verifikasi</span>
                    </div>
                </div>
                <input type="hidden" name="captcha_token" id="captcha_token">
            </div>

            <button type="submit" class="btn btn-primary btn-full pulse-glow" id="btn-login" disabled>
                <span>Masuk</span>
            </button>
        </form>

        <div class="auth-links">
            <a href="<?= BASE_URL ?>/auth/forgot_password.php">Lupa Password?</a>
            <span class="divider">|</span>
            <a href="<?= BASE_URL ?>/auth/register.php">Daftar Baru</a>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/slider-captcha.js"></script>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>

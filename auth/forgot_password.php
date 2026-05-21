<?php
/**
 * NOXARA - Forgot Password
 */
require_once __DIR__ . '/../config/bootstrap.php';

if (current_user()) redirect(BASE_URL . '/pages/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid.';
    } elseif (!captcha_is_verified()) {
        $error = 'Selesaikan verifikasi captcha terlebih dahulu.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email tidak valid.';
        } else {
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = db()->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $user['id'], $token, $expires);
                $stmt->execute();
                $stmt->close();
                
                // In production, send email. For now show link.
                $reset_link = BASE_URL . '/auth/reset_password.php?token=' . $token;
                $success = 'Link reset password telah dikirim ke email Anda. Cek inbox/spam.';
            } else {
                // Don't reveal if email exists
                $success = 'Jika email terdaftar, link reset akan dikirim.';
            }
            captcha_consume();
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
    <title>Lupa Password | NOXARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="theme-dark auth-page">
<div class="auth-container">
    <div class="auth-header">
        <h1 class="logo-brand orbitron gradient-text">NOXARA</h1>
    </div>

    <div class="auth-card glassmorphism">
        <h2 class="auth-title">Lupa Password</h2>
        <p class="auth-desc">Masukkan email terdaftar untuk reset password.</p>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <form method="POST" id="forgot-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="email@contoh.com" required>
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

            <button type="submit" class="btn btn-primary btn-full" id="btn-submit" disabled>
                <span>Kirim Link Reset</span>
            </button>
        </form>

        <div class="auth-links">
            <a href="<?= BASE_URL ?>/auth/login.php">Kembali ke Login</a>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/slider-captcha.js"></script>
</body>
</html>

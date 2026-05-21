<?php
/**
 * NOXARA - Register Page
 */
require_once __DIR__ . '/../config/bootstrap.php';

if (current_user()) redirect(BASE_URL . '/pages/dashboard.php');

$error = '';
$success = '';
$ref_code = $_GET['ref'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid.';
    } elseif (!captcha_is_verified()) {
        $error = 'Selesaikan verifikasi captcha terlebih dahulu.';
    } else {
        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'password' => $_POST['password'] ?? '',
            'referral_code' => $_POST['referral_code'] ?? ''
        ];
        
        $confirm = $_POST['password_confirm'] ?? '';
        if ($data['password'] !== $confirm) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $result = register_user($data);
            if ($result['success']) {
                captcha_consume();
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['login_time'] = time();
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
    <title>Daftar | NOXARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="theme-dark auth-page">
<div class="auth-container">
    <div class="auth-header">
        <h1 class="logo-brand orbitron gradient-text">NOXARA</h1>
        <p class="auth-tagline">Daftar & Dapatkan Bonus Rp 10.000</p>
    </div>

    <div class="auth-card glassmorphism">
        <h2 class="auth-title">Buat Akun</h2>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="register-form" autocomplete="off">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Min. 4 karakter" required minlength="4" value="<?= sanitize($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="email@contoh.com" required value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="phone">No. HP (opsional)</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="08xxxxxxxxxx" value="<?= sanitize($_POST['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 karakter" required minlength="6">
                    <button type="button" class="btn-toggle-pw" onclick="togglePassword('password')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password" required>
            </div>

            <div class="form-group">
                <label for="referral_code">Kode Referral (opsional)</label>
                <input type="text" id="referral_code" name="referral_code" class="form-input" placeholder="REF-XXXXXX" value="<?= sanitize($ref_code ?: ($_POST['referral_code'] ?? '')) ?>">
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

            <button type="submit" class="btn btn-primary btn-full pulse-glow" id="btn-register" disabled>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <div class="auth-links">
            <span>Sudah punya akun?</span>
            <a href="<?= BASE_URL ?>/auth/login.php">Masuk</a>
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

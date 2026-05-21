<?php
/**
 * NOXARA - Reset Password
 */
require_once __DIR__ . '/../config/bootstrap.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$valid = false;

if ($token) {
    $stmt = db()->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $reset = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $valid = (bool)$reset;
}

if (!$valid && !$success) {
    $error = 'Link reset tidak valid atau sudah expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        
        if (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($password !== $confirm) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashed, $reset['user_id']);
            $stmt->execute();
            $stmt->close();
            
            $stmt = db()->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->bind_param('i', $reset['id']);
            $stmt->execute();
            $stmt->close();
            
            $success = 'Password berhasil direset! Silakan login dengan password baru.';
            $valid = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0A0E1A">
    <title>Reset Password | NOXARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="theme-dark auth-page">
<div class="auth-container">
    <div class="auth-header">
        <h1 class="logo-brand orbitron gradient-text">NOXARA</h1>
    </div>

    <div class="auth-card glassmorphism">
        <h2 class="auth-title">Reset Password</h2>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= sanitize($success) ?></div>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary btn-full">Ke Halaman Login</a>
        <?php endif; ?>

        <?php if ($valid): ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 karakter" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password baru" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="auth-links">
            <a href="<?= BASE_URL ?>/auth/login.php">Kembali ke Login</a>
        </div>
    </div>
</div>
</body>
</html>

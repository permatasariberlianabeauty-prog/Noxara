<?php
/**
 * NOXARA - Security Settings
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Keamanan';
$show_back = true;

$msg = '';
$msg_type = '';

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $msg = 'Password lama salah.';
        $msg_type = 'error';
    } elseif (strlen($new_pass) < 6) {
        $msg = 'Password baru minimal 6 karakter.';
        $msg_type = 'error';
    } elseif ($new_pass !== $confirm) {
        $msg = 'Konfirmasi password tidak cocok.';
        $msg_type = 'error';
    } else {
        $hash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, $user['id']);
        $stmt->execute();
        $stmt->close();
        $msg = 'Password berhasil diubah.';
        $msg_type = 'success';
    }
}

// Handle set/change PIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_pin') {
    verify_csrf();
    $new_pin = $_POST['new_pin'] ?? '';
    $confirm_pin = $_POST['confirm_pin'] ?? '';

    if (!preg_match('/^\d{6}$/', $new_pin)) {
        $msg = 'PIN harus 6 digit angka.';
        $msg_type = 'error';
    } elseif ($new_pin !== $confirm_pin) {
        $msg = 'Konfirmasi PIN tidak cocok.';
        $msg_type = 'error';
    } else {
        $pin_hash = password_hash($new_pin, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = db()->prepare("UPDATE users SET pin = ? WHERE id = ?");
        $stmt->bind_param('si', $pin_hash, $user['id']);
        $stmt->execute();
        $stmt->close();
        $msg = 'PIN berhasil disimpan.';
        $msg_type = 'success';
    }
}

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section">
    <div class="section-header">
        <h2 class="section-title">Keamanan Akun</h2>
    </div>

    <?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?>"><?= sanitize($msg) ?></div>
    <?php endif; ?>


    <!-- Change Password -->
    <div class="glassmorphism form-card">
        <h4>Ubah Password</h4>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-input" minlength="6" required>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-input" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Ubah Password</button>
        </form>
    </div>

    <!-- Set/Change PIN -->
    <div class="glassmorphism form-card">
        <h4>Set/Ubah PIN Transaksi</h4>
        <p class="text-muted">PIN 6 digit digunakan untuk konfirmasi withdraw.</p>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="set_pin">
            <div class="form-group">
                <label class="form-label">PIN Baru (6 digit)</label>
                <input type="password" name="new_pin" class="form-input" maxlength="6" pattern="\d{6}" inputmode="numeric" required>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi PIN</label>
                <input type="password" name="confirm_pin" class="form-input" maxlength="6" pattern="\d{6}" inputmode="numeric" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan PIN</button>
        </form>
    </div>
</section>

<?php include INCLUDES_PATH . '/footer.php'; ?>

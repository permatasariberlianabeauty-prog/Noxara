<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = db()->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $now = date('Y-m-d H:i:s');
        db()->query("UPDATE admin_users SET last_login = '{$now}' WHERE id = {$admin['id']}");
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | NOXARA</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Plus Jakarta Sans',sans-serif;background:#0A0E1A;color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center}.login-card{background:#0F1629;border:1px solid #1a2340;border-radius:16px;padding:40px;width:100%;max-width:380px}.login-card h1{color:#00D4FF;text-align:center;margin-bottom:8px;font-size:24px}.login-card p{color:#888;text-align:center;margin-bottom:24px;font-size:13px}.form-group{margin-bottom:16px}.form-group label{display:block;font-size:12px;color:#888;margin-bottom:4px}.form-group input{width:100%;padding:12px;background:#0A0E1A;border:1px solid #1a2340;border-radius:8px;color:#e0e0e0;font-size:14px}.btn{width:100%;padding:12px;background:#00D4FF;color:#0A0E1A;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer}.btn:hover{background:#00b8d4}.error{background:rgba(255,68,68,0.1);border:1px solid #ff4444;color:#ff4444;padding:10px;border-radius:8px;margin-bottom:16px;font-size:13px}
    </style>
</head>
<body>
<div class="login-card">
    <h1>NOXARA</h1>
    <p>Admin Panel</p>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Username</label><input type="text" name="username" required autofocus></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit" class="btn">Masuk</button>
    </form>
</div>
</body>
</html>

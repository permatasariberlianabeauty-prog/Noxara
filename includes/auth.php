<?php
/**
 * NOXARA - Authentication Functions
 */

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user !== null) return $user;
    
    $stmt = db()->prepare("SELECT u.*, uw.balance, uw.bonus_balance, uw.total_earned, uw.total_withdrawn FROM users u LEFT JOIN user_wallets uw ON uw.user_id = u.id WHERE u.id = ? AND u.status = 'active'");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        session_destroy();
        return null;
    }
    return $user;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Sesi habis. Silakan login ulang.'], 401);
        }
        redirect(BASE_URL . '/auth/login.php');
    }
    return $user;
}

function attempt_login(string $username, string $password): array {
    // Check rate limit
    $stmt = db()->prepare("SELECT login_attempts, locked_until FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $check = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($check && $check['locked_until'] && strtotime($check['locked_until']) > time()) {
        $mins = ceil((strtotime($check['locked_until']) - time()) / 60);
        return ['success' => false, 'message' => "Akun terkunci. Coba lagi dalam {$mins} menit."];
    }
    
    $stmt = db()->prepare("SELECT id, username, password, status, login_attempts FROM users WHERE (username = ? OR email = ?) LIMIT 1");
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed attempt
        log_login($user['id'] ?? null, $username, 'failed');
        
        if ($user) {
            $attempts = $user['login_attempts'] + 1;
            if ($attempts >= LOGIN_MAX_ATTEMPTS) {
                $lock = date('Y-m-d H:i:s', strtotime('+' . LOGIN_LOCKOUT_MINUTES . ' minutes'));
                $stmt = db()->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?");
                $stmt->bind_param('isi', $attempts, $lock, $user['id']);
            } else {
                $stmt = db()->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                $stmt->bind_param('ii', $attempts, $user['id']);
            }
            $stmt->execute();
            $stmt->close();
        }
        return ['success' => false, 'message' => 'Username/email atau password salah.'];
    }
    
    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Akun Anda diblokir. Hubungi admin.'];
    }
    
    // Success - reset attempts, set session
    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = ? WHERE id = ?");
    $stmt->bind_param('si', $now, $user['id']);
    $stmt->execute();
    $stmt->close();
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
    
    log_login($user['id'], $username, 'success');
    record_session($user['id']);
    
    return ['success' => true, 'message' => 'Login berhasil!'];
}

function do_logout(): void {
    if (!empty($_SESSION['user_id'])) {
        $stmt = db()->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_id = ?");
        $sid = session_id();
        $stmt->bind_param('is', $_SESSION['user_id'], $sid);
        $stmt->execute();
        $stmt->close();
    }
    session_destroy();
}

function register_user(array $data): array {
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $ref_code = trim($data['referral_code'] ?? '');
    
    // Validation
    if (strlen($username) < 4) return ['success' => false, 'message' => 'Username minimal 4 karakter.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['success' => false, 'message' => 'Email tidak valid.'];
    if (strlen($password) < 6) return ['success' => false, 'message' => 'Password minimal 6 karakter.'];
    
    // Check duplicates
    $stmt = db()->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Username atau email sudah terdaftar.'];
    }
    $stmt->close();
    
    // Find referrer
    $referred_by = null;
    if ($ref_code) {
        $stmt = db()->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param('s', $ref_code);
        $stmt->execute();
        $ref = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($ref) $referred_by = $ref['id'];
    }
    
    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    $my_ref_code = generate_referral_code();
    
    db()->begin_transaction();
    try {
        $stmt = db()->prepare("INSERT INTO users (username, email, phone, password, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssi', $username, $email, $phone, $hashed, $my_ref_code, $referred_by);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();
        
        // Create wallet with registration bonus
        $bonus = (float)REGISTRATION_BONUS;
        $stmt = db()->prepare("INSERT INTO user_wallets (user_id, bonus_balance) VALUES (?, ?)");
        $stmt->bind_param('id', $user_id, $bonus);
        $stmt->execute();
        $stmt->close();
        
        // Record bonus in ledger
        require_once INCLUDES_PATH . '/ledger.php';
        ledger_write($user_id, 'bonus_balance', 'credit', $bonus, 0, $bonus, 'Bonus pendaftaran', 'registration', $user_id);
        
        // Build referral chain
        if ($referred_by) {
            require_once INCLUDES_PATH . '/referral.php';
            build_referral_chain($user_id, $referred_by);
        }
        
        // Achievement: First Login
        require_once INCLUDES_PATH . '/achievement.php';
        check_achievement($user_id, 'first_login');
        
        db()->commit();
        return ['success' => true, 'message' => 'Registrasi berhasil! Bonus Rp 10.000 telah ditambahkan.', 'user_id' => $user_id];
    } catch (Exception $e) {
        db()->rollback();
        error_log('Register error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan. Coba lagi.'];
    }
}

function log_login(?int $user_id, string $username, string $status): void {
    $ip = get_client_ip();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = db()->prepare("INSERT INTO user_login_logs (user_id, username, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $user_id, $username, $ip, $ua, $status);
    $stmt->execute();
    $stmt->close();
}

function record_session(int $user_id): void {
    $sid = session_id();
    $ip = get_client_ip();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = parse_device($ua);
    $stmt = db()->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, device_info) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $user_id, $sid, $ip, $ua, $device);
    $stmt->execute();
    $stmt->close();
}

function parse_device(string $ua): string {
    if (preg_match('/Mobile|Android|iPhone/i', $ua)) return 'Mobile';
    if (preg_match('/Tablet|iPad/i', $ua)) return 'Tablet';
    return 'Desktop';
}

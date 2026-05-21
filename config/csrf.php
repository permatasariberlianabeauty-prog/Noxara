<?php
/**
 * NOXARA - CSRF Protection
 */

function csrf_generate(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_time']) || 
        (time() - $_SESSION['csrf_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_generate() . '">';
}

function csrf_verify(?string $token = null): bool {
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    if ((time() - ($_SESSION['csrf_time'] ?? 0)) > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    return true;
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Muat ulang halaman.']));
    }
}

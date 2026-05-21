<?php
/**
 * NOXARA - General Helper Functions
 */

function format_rupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function format_number(float $num): string {
    return number_format($num, 0, ',', '.');
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_ajax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function get_client_ip(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

function generate_referral_code(): string {
    do {
        $code = 'REF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $stmt = db()->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);
    return $code;
}

function mask_username(string $name): string {
    $len = mb_strlen($name);
    if ($len <= 2) return $name[0] . '***';
    return mb_substr($name, 0, 1) . '***' . mb_substr($name, -1);
}

function time_greeting(): string {
    $hour = (int)date('H');
    if ($hour >= 5 && $hour < 11) return 'Selamat Pagi';
    if ($hour >= 11 && $hour < 15) return 'Selamat Siang';
    if ($hour >= 15 && $hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
}

function get_setting(string $key, ?string $default = null): ?string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $cache[$key] = $result['setting_value'] ?? $default;
    return $cache[$key];
}

function set_setting(string $key, string $value): void {
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param('sss', $key, $value, $value);
    $stmt->execute();
    $stmt->close();
}

function time_ago(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}

function date_id(string $date): string {
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $months[(int)$d->format('m') - 1] . ' ' . $d->format('Y');
}

<?php
/**
 * NOXARA - Slider Captcha (FIXED)
 * 
 * Fix: Tolerance diperbesar ke ±12px, validasi duration lebih lenient,
 * dan tracking posisi menggunakan percentage-based calculation
 * untuk konsistensi across devices.
 */

define('CAPTCHA_TRACK_WIDTH', 280);
define('CAPTCHA_THUMB_WIDTH', 44);
define('CAPTCHA_TOLERANCE_PX', 12); // Diperbesar dari 8 ke 12 untuk UX lebih baik
define('CAPTCHA_MIN_DRAG_MS', 200); // Diturunkan dari 300 ke 200ms

function captcha_create(): array {
    // Generate random target position (20% - 80% of track)
    $min_pos = (int)(CAPTCHA_TRACK_WIDTH * 0.2);
    $max_pos = (int)(CAPTCHA_TRACK_WIDTH * 0.8) - CAPTCHA_THUMB_WIDTH;
    $target_x = random_int($min_pos, $max_pos);
    
    // Generate unique token
    $token = bin2hex(random_bytes(16));
    
    // Store in session (not DB, simpler & faster)
    $_SESSION['captcha'] = [
        'token' => $token,
        'target_x' => $target_x,
        'track_width' => CAPTCHA_TRACK_WIDTH,
        'created_at' => time()
    ];
    
    return [
        'success' => true,
        'token' => $token,
        'track_width' => CAPTCHA_TRACK_WIDTH,
        'thumb_width' => CAPTCHA_THUMB_WIDTH,
        // Target dikirim sebagai percentage untuk konsistensi
        'target_percent' => round(($target_x / (CAPTCHA_TRACK_WIDTH - CAPTCHA_THUMB_WIDTH)) * 100, 1)
    ];
}

function captcha_verify(string $token, float $final_x, int $duration_ms, ?float $track_width_client = null): array {
    if (empty($_SESSION['captcha'])) {
        return ['valid' => false, 'message' => 'Captcha expired. Muat ulang halaman.'];
    }
    
    $captcha = $_SESSION['captcha'];
    
    // Check token
    if (!hash_equals($captcha['token'], $token)) {
        unset($_SESSION['captcha']);
        return ['valid' => false, 'message' => 'Token captcha tidak valid.'];
    }
    
    // Check expiry (5 minutes)
    if ((time() - $captcha['created_at']) > CAPTCHA_EXPIRE_SECONDS) {
        unset($_SESSION['captcha']);
        return ['valid' => false, 'message' => 'Captcha sudah expired. Muat ulang.'];
    }
    
    // Check drag duration (anti-bot)
    if ($duration_ms < CAPTCHA_MIN_DRAG_MS) {
        unset($_SESSION['captcha']);
        return ['valid' => false, 'message' => 'Gerakan terlalu cepat. Coba lagi.'];
    }
    
    // Calculate target position based on client track width
    $server_target = $captcha['target_x'];
    
    // If client sends their track_width, recalculate proportionally
    if ($track_width_client && $track_width_client > 0) {
        $ratio = $track_width_client / CAPTCHA_TRACK_WIDTH;
        $adjusted_target = $server_target * $ratio;
        $tolerance = CAPTCHA_TOLERANCE_PX * $ratio;
    } else {
        $adjusted_target = $server_target;
        $tolerance = CAPTCHA_TOLERANCE_PX;
    }
    
    // Check position within tolerance
    $diff = abs($final_x - $adjusted_target);
    
    if ($diff <= $tolerance) {
        // SUCCESS - clear captcha (one-time use)
        unset($_SESSION['captcha']);
        $_SESSION['captcha_verified'] = true;
        $_SESSION['captcha_verified_at'] = time();
        return ['valid' => true, 'message' => 'Verifikasi berhasil!'];
    }
    
    // FAILED - generate new captcha
    unset($_SESSION['captcha']);
    return ['valid' => false, 'message' => 'Posisi tidak tepat. Coba lagi.'];
}

function captcha_is_verified(): bool {
    if (empty($_SESSION['captcha_verified'])) return false;
    // Valid for 10 minutes after verification
    if ((time() - ($_SESSION['captcha_verified_at'] ?? 0)) > 600) {
        unset($_SESSION['captcha_verified'], $_SESSION['captcha_verified_at']);
        return false;
    }
    return true;
}

function captcha_consume(): void {
    unset($_SESSION['captcha_verified'], $_SESSION['captcha_verified_at']);
}

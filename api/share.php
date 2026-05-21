<?php
/**
 * NOXARA - Share API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'text':
        require_once INCLUDES_PATH . '/wallet.php';
        $w = get_wallet($user['id']);
        $total_earned = format_rupiah($w['total_earned']);
        $ref_code = $user['referral_code'];
        $link = BASE_URL . '/auth/register.php?ref=' . $ref_code;

        $text = "Saya investasi di NOXARA dan sudah profit {$total_earned}! Platform mining digital terpercaya. Daftar pakai kode {$ref_code} dan dapatkan bonus Rp 10.000!\n\n{$link}";

        json_response([
            'success' => true,
            'text' => $text,
            'whatsapp_url' => 'https://wa.me/?text=' . urlencode($text),
            'telegram_url' => 'https://t.me/share/url?url=' . urlencode($link) . '&text=' . urlencode($text)
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

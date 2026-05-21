<?php
/**
 * NOXARA - Community Chat API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Check min VIP for chat
$min_vip = (int)get_setting('chat_min_vip', '0');
if ($user['vip_level'] < $min_vip && $action === 'send') {
    json_response(['success' => false, 'message' => "Chat hanya untuk VIP {$min_vip} ke atas."]);
}

switch ($action) {
    case 'list':
        $limit = (int)($_GET['limit'] ?? 50);
        $after_id = (int)($_GET['after_id'] ?? 0);

        if ($after_id > 0) {
            $stmt = db()->prepare("SELECT cm.*, u.username, u.vip_level, u.avatar FROM community_chat_messages cm JOIN users u ON u.id = cm.user_id WHERE cm.is_deleted = 0 AND cm.id > ? ORDER BY cm.id ASC LIMIT ?");
            $stmt->bind_param('ii', $after_id, $limit);
        } else {
            $stmt = db()->prepare("SELECT cm.*, u.username, u.vip_level, u.avatar FROM community_chat_messages cm JOIN users u ON u.id = cm.user_id WHERE cm.is_deleted = 0 ORDER BY cm.id DESC LIMIT ?");
            $stmt->bind_param('i', $limit);
        }
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($after_id <= 0) $messages = array_reverse($messages);

        json_response(['success' => true, 'data' => $messages]);
        break;

    case 'send':
        $message = trim($input['message'] ?? '');
        if (empty($message) || strlen($message) > 500) {
            json_response(['success' => false, 'message' => 'Pesan tidak valid (1-500 karakter).']);
        }

        // Rate limit: 1 message per 30 seconds
        $stmt = db()->prepare("SELECT id FROM community_chat_messages WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            json_response(['success' => false, 'message' => 'Tunggu 30 detik sebelum mengirim pesan lagi.']);
        }
        $stmt->close();

        $clean = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $stmt = db()->prepare("INSERT INTO community_chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bind_param('is', $user['id'], $clean);
        $stmt->execute();
        $msg_id = $stmt->insert_id;
        $stmt->close();

        json_response(['success' => true, 'id' => $msg_id, 'message' => 'Pesan terkirim.']);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

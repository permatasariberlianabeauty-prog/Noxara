<?php
/**
 * NOXARA - Notification System
 */

function notify_user(int $user_id, string $title, string $message, string $type = 'system', ?string $link = null): void {
    $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $user_id, $title, $message, $type, $link);
    $stmt->execute();
    $stmt->close();
}

function notify_broadcast(string $title, string $message, string $type = 'system'): void {
    $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, type, is_broadcast) VALUES (NULL, ?, ?, ?, 1)");
    $stmt->bind_param('sss', $title, $message, $type);
    $stmt->execute();
    $stmt->close();
}

function get_unread_count(int $user_id): int {
    $stmt = db()->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE (user_id = ? OR is_broadcast = 1) AND is_read = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$r['cnt'];
}

function get_notifications(int $user_id, int $limit = 20, int $offset = 0): array {
    $stmt = db()->prepare("SELECT * FROM notifications WHERE user_id = ? OR is_broadcast = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

function mark_notifications_read(int $user_id): void {
    $stmt = db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

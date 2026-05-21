<?php
/**
 * NOXARA - Activity Feed
 */

function add_activity(?int $user_id, string $message, string $type = 'system', bool $is_fake = false): void {
    $fake = $is_fake ? 1 : 0;
    $stmt = db()->prepare("INSERT INTO activity_feed (user_id, message, type, is_fake) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('issi', $user_id, $message, $type, $fake);
    $stmt->execute();
    $stmt->close();
}

function get_activity_feed(int $limit = 20): array {
    $stmt = db()->prepare("SELECT * FROM activity_feed ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

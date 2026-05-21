<?php
/**
 * NOXARA - Notification API
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
require_once INCLUDES_PATH . '/notification.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'unread_count':
        json_response(['success' => true, 'count' => get_unread_count($user['id'])]);
        break;

    case 'list':
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = (int)($_GET['offset'] ?? 0);
        $notifs = get_notifications($user['id'], $limit, $offset);
        json_response(['success' => true, 'data' => $notifs]);
        break;

    case 'mark_read':
        mark_notifications_read($user['id']);
        json_response(['success' => true]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

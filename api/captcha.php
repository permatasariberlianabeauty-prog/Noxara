<?php
/**
 * NOXARA - Captcha API
 */
require_once __DIR__ . '/../config/bootstrap.php';
require_once INCLUDES_PATH . '/captcha.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $result = captcha_create();
        json_response($result);
        break;
        
    case 'verify':
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $final_x = (float)($input['final_x'] ?? 0);
        $duration = (int)($input['duration_ms'] ?? 0);
        $track_width = (float)($input['track_width'] ?? 0);
        
        $result = captcha_verify($token, $final_x, $duration, $track_width);
        json_response($result);
        break;
        
    default:
        json_response(['success' => false, 'message' => 'Action tidak valid.'], 400);
}

<?php
// Public API router 
// This file calls the appropriate backend file

require_once __DIR__ . '/../backend/auth/session.php';
require_once __DIR__ . '/../backend/auth/sanitize.php';

$action = cleanString($_GET['action'] ?? '');

switch ($action) {
    case 'send_invite':
        require_once __DIR__ . '/../backend/admin/send_invite.php';
        break;

    case 'get_agencies':
        require_once __DIR__ . '/../backend/admin/get_agencies.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown action']);
        break;
}
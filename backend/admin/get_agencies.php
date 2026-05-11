<?php
// backend/admin/get_agencies.php
// Returns all active agencies as JSON — used to populate the invite form dropdown

require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth/session.php';

header('Content-Type: application/json');

requireAdmin();

$db   = db();
$stmt = $db->prepare("
    SELECT agency_id, name
    FROM agencies
    WHERE is_active = true
    ORDER BY name ASC
");
$stmt->execute();
$agencies = $stmt->fetchAll();

echo json_encode(['success' => true, 'agencies' => $agencies]);
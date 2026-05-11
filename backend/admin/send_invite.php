<?php
require_once __DIR__ . '/../../backend/db.php';
require_once __DIR__ . '/../../backend/auth/session.php';
require_once __DIR__ . '/../../backend/auth/sanitize.php';

header('Content-Type: application/json');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email    = cleanEmail($_POST['email']     ?? '');
$agencyId = cleanInt($_POST['agency_id']   ?? 0);
$role     = cleanString($_POST['role']     ?? 'agency');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid email address is required']);
    exit;
}

if ($agencyId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid agency must be selected']);
    exit;
}

if (!in_array($role, ['admin', 'agency'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

$db = db();

// Check agency exists
$check = $db->prepare("SELECT agency_id FROM agencies WHERE agency_id = ? AND is_active = true LIMIT 1");
$check->execute([$agencyId]);
if (!$check->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Agency not found']);
    exit;
}

// Check no pending invite already exists for this email
$existing = $db->prepare("
    SELECT id FROM invitations
    WHERE email = ? AND used_at IS NULL AND expires_at > NOW()
    LIMIT 1
");
$existing->execute([$email]);
if ($existing->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'A pending invite already exists for this email']);
    exit;
}

// Generate token
$token     = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+72 hours'));
$invitedBy = currentAccountId();

// Store invite
$stmt = $db->prepare("
    INSERT INTO invitations (token, email, agency_id, role, invited_by, expires_at, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$token, $email, $agencyId, $role, $invitedBy, $expiresAt]);

// Build invite link
$inviteLink = 'https://www.120580.xyz/register.php?token=' . $token;

// Send email
$subject = 'You have been invited to manage a listing on CAPK 2-1-1';
$message = "Hello,\n\n"
    . "You have been invited to create an account on the CAPK 2-1-1 Kern County Resource Directory.\n\n"
    . "Click the link below to set up your account. This link expires in 72 hours and can only be used once.\n\n"
    . $inviteLink . "\n\n"
    . "If you did not expect this invitation, you can safely ignore this email.\n\n"
    . "— CAPK 2-1-1 Team";

$headers = "From: noreply@120580.xyz\r\nReply-To: noreply@120580.xyz";
$sent    = mail($email, $subject, $message, $headers);

if (!$sent) {
    error_log("Invite email failed for: $email — link: $inviteLink");
    echo json_encode([
        'success'     => true,
        'warning'     => 'Invite created but email could not be sent. Copy the link manually.',
        'invite_link' => $inviteLink,
        'expires_at'  => $expiresAt
    ]);
    exit;
}

echo json_encode([
    'success'     => true,
    'message'     => "Invite sent to $email",
    'invite_link' => $inviteLink,
    'expires_at'  => $expiresAt
]);
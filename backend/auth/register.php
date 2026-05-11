<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/invite.php';

function registerFromInvite(string $token, string $firstName, string $lastName, string $password, string $confirm): array {

    // Validate token
    $invite = getValidInvite($token);
    if (!$invite) {
        return ['error' => 'This invite link is invalid, has already been used, or has expired'];
    }

    // Validate fields
    $firstName = cleanString($firstName);
    $lastName  = cleanString($lastName);

    if (empty($firstName) || empty($lastName)) {
        return ['error' => 'First and last name are required'];
    }

    if (strlen($password) < 8) {
        return ['error' => 'Password must be at least 8 characters'];
    }

    if ($password !== $confirm) {
        return ['error' => 'Passwords do not match'];
    }

    $db           = db();
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Create the account
    $stmt = $db->prepare("
        INSERT INTO accounts (agency_id, email, password_hash, first_name, last_name, role, is_active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, true, NOW(), NOW())
    ");
    $stmt->execute([
        $invite['agency_id'],
        $invite['email'],
        $passwordHash,
        $firstName,
        $lastName,
        $invite['role']
    ]);

    $newAccountId = (int) $db->lastInsertId();

    // Mark invite as used — link is now dead
    markInviteUsed($invite['id']);

    // Log them in
    startSession();
    $_SESSION['account_id'] = $newAccountId;
    $_SESSION['role']       = $invite['role'];
    $_SESSION['agency_id']  = $invite['agency_id'];
    $_SESSION['email']      = $invite['email'];
    $_SESSION['first_name'] = $firstName;

    $redirect = $invite['role'] === 'admin' ? '/admin-dashboard.php' : '/agency-dashboard.php';

    return ['success' => true, 'redirect' => $redirect];
}
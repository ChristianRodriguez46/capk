<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/sanitize.php';

function login(string $email, string $password): array {
    $email = cleanEmail($email);

    if (empty($email) || empty($password)) {
        return ['error' => 'Email and password are required'];
    }

    $db   = db();
    $stmt = $db->prepare("
        SELECT aid, email, password_hash, first_name, last_name, role, agency_id, is_active
        FROM accounts
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $account = $stmt->fetch();

    // No account found or wrong password
    if (!$account || !password_verify($password, $account['password_hash'])) {
        return ['error' => 'Invalid email or password'];
    }

    // Account is deactivated
    if (!$account['is_active']) {
        return ['error' => 'This account has been deactivated'];
    }

    // Set session
    startSession();
    $_SESSION['account_id'] = $account['aid'];
    $_SESSION['role']       = $account['role'];
    $_SESSION['agency_id']  = $account['agency_id'];
    $_SESSION['email']      = $account['email'];
    $_SESSION['first_name'] = $account['first_name'];

    // Update last login timestamp
    $update = $db->prepare("UPDATE accounts SET last_login_at = NOW() WHERE aid = ?");
    $update->execute([$account['aid']]);

    // Redirect based on role
    $redirect = $account['role'] === 'admin' ? '/admin-dashboard.php' : '/agency-dashboard.php';

    return [
        'success'    => true,
        'role'       => $account['role'],
        'first_name' => $account['first_name'],
        'redirect'   => $redirect
    ];
}
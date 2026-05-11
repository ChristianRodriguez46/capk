<?php
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAdmin(): void {
    startSession();
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die(json_encode(['error' => 'Admin access required']));
    }
}

function requireAgency(): void {
    startSession();
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'agency') {
        http_response_code(403);
        die(json_encode(['error' => 'Agency access required']));
    }
}

function requireAuth(): void {
    startSession();
    if (empty($_SESSION['account_id'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Authentication required']));
    }
}

function currentAccountId(): int {
    startSession();
    return (int) ($_SESSION['account_id'] ?? 0);
}

function currentAgencyId(): int {
    startSession();
    return (int) ($_SESSION['agency_id'] ?? 0);
}

function currentRole(): string {
    startSession();
    return $_SESSION['role'] ?? '';
}
<?php
require_once __DIR__ . '/../db.php';

function getValidInvite(string $token): ?array {
    $db = db();
    $stmt = $db->prepare("
        SELECT id, token, email, agency_id, role, invited_by, used_at, expires_at
        FROM invitations
        WHERE token = ?
          AND used_at IS NULL
          AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function markInviteUsed(int $inviteId): void {
    $db = db();
    $stmt = $db->prepare("UPDATE invitations SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$inviteId]);
}
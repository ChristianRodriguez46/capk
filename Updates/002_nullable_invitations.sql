-- Allow admin invites with no agency and make invites with no inviter
-- Date: 2026-09-04

ALTER TABLE invitations
  MODIFY agency_id INT NULL,
  MODIFY invited_by INT NULL;

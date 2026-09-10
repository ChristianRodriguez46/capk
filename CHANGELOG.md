# Changelog

## September 4, 2026

- Invitations table now allows null `agency_id` and `invited_by`. Needed this so admins can be invited without picking an agency, and so the first admin account can be created without an inviter.
- Added seed invite data for the 5 real agencies plus an admin invite.
- Fixed `send_invite.php` — it was still requiring an agency even for admin invites.
- Added an admin/agency toggle to the invite form. Agency dropdown hides when admin is selected.
- Tested full invite -> register flow for both agency and admin accounts.
- Still need: list invites, revoke invite, resend invite endpoints. Drafted but not deployed yet.
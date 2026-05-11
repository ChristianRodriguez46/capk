# Invite System — How It Works

## Overview

The invite system allows a CAPK admin to onboard new agency users without ever creating their password for them. The admin sends a one-time invite link to an agency contact. That person clicks the link, sets their own name and password, and gets immediate access to the Agency Portal. The link expires after 72 hours and can only be used once.

---

## Files Involved

| File               | Location                        | Purpose                                                            |
|--------------------|---------------------------------|--------------------------------------------------------------------|
| `db.php`           | `backend/db.php`                | PDO database connection used by all backend files                  |
| `sanitize.php`     | `backend/auth/sanitize.php`     | Cleans all user input before it touches the database               |
| `session.php`      | `backend/auth/session.php`      | Starts sessions, checks roles, provides helper functions           |
| `invite.php`       | `backend/auth/invite.php`       | Validates invite tokens and marks them as used                     |
| `register.php`     | `backend/auth/register.php`     | Contains the `registerFromInvite()` function that creates accounts |
| `send_invite.php`  | `backend/admin/send_invite.php` | Admin endpoint — generates token, stores it, emails the link       |
| `login.php`        | `backend/auth/login.php`        | Verifies credentials, sets session, redirects by role              |
| `www/login.php`    | `www/login.php`                 | Public login page (HTML form + POST handler)                       |
| `www/register.php` | `www/register.php`              | Public registration page the invite link opens                     |

---

## The Flow Step by Step

```
Admin logs in at https://www.120580.xyz/login.php
        ↓
Admin sends invite via send_invite.php (POST)
  — email, agency_id, role sent as POST data
  — 64-character random token generated
  — token stored in invitations table with 72-hour expiry
  — invite link emailed to the agency contact:
    https://www.120580.xyz/register.php?token=abc123...
        ↓
Agency contact clicks the link (GET request to www/register.php)
  — token passed to getValidInvite()
  — checks: token exists + used_at IS NULL + expires_at > NOW()
  — if valid: shows the registration form with email pre-filled
  — if invalid/expired: shows error page
        ↓
Agency contact fills in first name, last name, password (POST)
  — registerFromInvite() is called
  — password hashed with password_hash() / PASSWORD_BCRYPT
  — new row inserted into accounts table
  — invite marked as used (used_at = NOW())
  — session created automatically (user is logged in)
  — redirected to /agency-dashboard.php
        ↓
Token is now dead — clicking the link again shows "Invalid or Expired"
```

---

## Database Tables Used

### `invitations`
Stores every invite that has been sent.

| Column       | Type         | Purpose                                           |
|--------------|--------------|---------------------------------------------------|
| `id`         | INT          | Primary key                                       |
| `token`      | VARCHAR(64)  | The secret 64-character link code                 |
| `email`      | VARCHAR(255) | Who the invite was sent to                        |
| `agency_id`  | INT          | Which agency they will manage                     |
| `role`       | VARCHAR(20)  | Their role — always `agency` for now              |
| `invited_by` | INT          | Which admin account sent it                       |
| `used_at`    | DATETIME     | NULL = not used yet. Set when account is created. |
| `expires_at` | DATETIME     | Link dies after 72 hours                          |
| `created_at` | DATETIME     | When the invite was sent                          |

### `accounts`
Stores all user accounts — both admin and agency.

| Column          | Type         | Purpose                                         |
|-----------------|--------------|-------------------------------------------------|
| `aid`           | INT          | Primary key                                     |
| `agency_id`     | INT          | NULL for admins, set to agency for agency users |
| `email`         | VARCHAR(255) | Login email                                     |
| `password_hash` | VARCHAR(255) | bcrypt hash — plain text never stored           |
| `first_name`    | VARCHAR(100) | Set by user during registration                 |
| `last_name`     | VARCHAR(100) | Set by user during registration                 |
| `role`          | VARCHAR(20)  | `admin` or `agency`                             |
| `is_active`     | BOOLEAN      | Can deactivate without deleting                 |
| `last_login_at` | DATETIME     | Updated on every successful login               |

---

## Security Details

- **Tokens are 64 characters** generated with `bin2hex(random_bytes(32))` — cryptographically secure, not guessable
- **One-time use** — `used_at` is set the moment an account is created. Any subsequent click on the same link returns an error
- **72-hour expiry** — `expires_at > NOW()` is checked on every request. Expired links are automatically rejected
- **Passwords are never stored plain text** — `password_hash()` with `PASSWORD_BCRYPT` is used. Only the hash lives in the database
- **Pending invite check** — if an unused, unexpired invite already exists for an email, a duplicate cannot be sent
- **Role locked to invite** — the account role is taken from the invitation row, not from user input. A user cannot change their own role during registration
- **Session set server-side** — `agency_id` and `role` come from the session, never from URL parameters that could be tampered with

---

## Email Delivery

DigitalOcean blocks outbound port 25 (SMTP) by default on new droplets. This means PHP's built-in `mail()` function cannot send emails from the server. The invite token is still created and stored correctly in the database.

**Current workaround:** If the email fails, `send_invite.php` returns the invite link directly in the JSON response so an admin can copy and send it manually.


---

## Terminal First Approach



###  Full Invite Flow Through Webpages Yet

The invite system requires an **admin to be logged in** before they can send an invite. `send_invite.php` calls `requireAdmin()` at the top — if there is no valid admin session, it returns a 403 and does nothing.

For `requireAdmin()` to pass, the following must exist:

1. An admin account in the `accounts` table
2. A working login page that sets the session
3. A working admin dashboard with a form that POSTs to `send_invite.php`

We have 1 and 2 done. We do not have 3 yet — the admin dashboard UI has not been built. Without a dashboard form, there is no webpage the admin can visit to fill in an email address and click "Send Invite."

**This is why we used the terminal to simulate the invite** — we manually set the session variables in PHP and called `send_invite.php` directly, bypassing the need for a dashboard UI:

```bash
php -r "
require '/home/capk/backend/auth/session.php';
startSession();
\$_SESSION['account_id'] = 1;
\$_SESSION['role'] = 'admin';
\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_POST['email'] = 'someone@example.com';
\$_POST['agency_id'] = 1;
\$_POST['role'] = 'agency';
require '/home/capk/backend/admin/send_invite.php';
"
```

This lets us test that the backend logic is correct.

---

### What the Full Webpage Flow Will Look Like (Once Dashboard is Built)



```
Admin visits https://www.120580.xyz/login.php
        ↓
Admin fills in email + password → session created
        ↓
Admin redirected to /admin-dashboard.php
        ↓
Admin fills in invite form:
  — Email address of new agency user
  — Select agency from dropdown
  — Click "Send Invite"
        ↓
Form POSTs to /api.php → routes to backend/admin/send_invite.php
        ↓
Token generated, stored, link returned
        ↓
Admin copies link and sends manually (until email is fixed)
  or email is delivered automatically 
```


---

## How to Test the Full Flow

**Step 1 — Log in as admin**
```
https://www.120580.xyz/login.php
Email: 
Password: 
```

**Step 2 — Send an invite (CLI method until dashboard is built)**
```bash
php -r "
require '/home/capk/backend/auth/session.php';
startSession();
\$_SESSION['account_id'] = 1;
\$_SESSION['role'] = 'admin';
\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_POST['email'] = 'someone@example.com';
\$_POST['agency_id'] = 1;
\$_POST['role'] = 'agency';
require '/home/capk/backend/admin/send_invite.php';
"
```

**Step 3 — Grab the token from the database**
```sql
SELECT token FROM invitations WHERE used_at IS NULL ORDER BY created_at DESC LIMIT 1;
```

**Step 4 — Open the invite link**
```
https://www.120580.xyz/register.php?token=PASTE_TOKEN_HERE
```

**Step 5 — Fill in the form and submit**

**Step 6 — Verify the account was created and invite marked used**
```sql
SELECT aid, email, role, agency_id FROM accounts;
SELECT id, email, used_at FROM invitations;
```

---

## Current Status

| Feature                     | Status                       |
|-----------------------------|------------------------------|
| Token generation            | Working                      |
| Token stored in DB          | Working                      |
| Invite link validation      | Working                      |
| Registration form           | Working                      |
| Account creation            | Working                      |
| One-time use enforcement    | Working                      |
| 72-hour expiry              | Working                      |
| Session created on register | Working                      |
| Login page                  | Working                      |
| Email delivery              | Blocked by DigitalOcean SMTP |
| Admin dashboard UI          | Not built yet                |
| Agency dashboard UI         | Not built yet                |
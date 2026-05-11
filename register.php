<?php
require_once __DIR__ . '/../backend/auth/register.php';

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $result = registerFromInvite(
        $_POST['token']            ?? '',
        $_POST['first_name']       ?? '',
        $_POST['last_name']        ?? '',
        $_POST['password']         ?? '',
        $_POST['confirm_password'] ?? ''
    );
    if (isset($result['error'])) {
        http_response_code(400);
    }
    echo json_encode($result);
    exit;
}

// Handle GET — show the form
$token  = $_GET['token'] ?? '';
$invite = $token ? getValidInvite($token) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account — CAPK 2-1-1</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .register-container {
            max-width: 480px;
            margin: 60px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .register-container h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .register-container p.subtitle { color: #666; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.25rem; }
        .form-group input {
            width: 100%; padding: 0.6rem 0.8rem;
            border: 1px solid #ccc; border-radius: 4px;
            font-size: 1rem; box-sizing: border-box;
        }
        .btn {
            width: 100%; padding: 0.75rem;
            background: #c0392b; color: #fff;
            border: none; border-radius: 4px;
            font-size: 1rem; cursor: pointer; font-weight: 600;
        }
        .btn:hover { background: #a93226; }
        .error { color: #c0392b; margin-top: 1rem; font-weight: 600; }
        .expired { text-align: center; padding: 2rem; color: #666; }
    </style>
</head>
<body>

<div class="register-container">

<?php if (!$token || !$invite): ?>
    <div class="expired">
        <h2>Invalid or Expired Link</h2>
        <p>This invite link is invalid, has already been used, or has expired.</p>
        <p>Please contact your CAPK administrator for a new invite.</p>
    </div>

<?php else: ?>
    <h1>Create Your Account</h1>
    <p class="subtitle">You've been invited to manage a listing on CAPK 2-1-1.</p>
    <p><strong>Email:</strong> <?= htmlspecialchars($invite['email']) ?></p>

    <form id="registerForm">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn">Create Account</button>
        <div class="error" id="errorMsg"></div>
    </form>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form    = e.target;
            const data    = new FormData(form);
            const errorEl = document.getElementById('errorMsg');
            errorEl.textContent = '';

            const res  = await fetch('/register.php', { method: 'POST', body: data });
            const json = await res.json();

            if (json.error) {
                errorEl.textContent = json.error;
            } else if (json.success) {
                window.location.href = json.redirect;
            }
        });
    </script>
<?php endif; ?>

</div>
</body>
</html>
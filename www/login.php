<?php
require_once __DIR__ . '/../backend/auth/login.php';

// Handle POST — process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $result = login(
        $_POST['email']    ?? '',
        $_POST['password'] ?? ''
    );
    if (isset($result['error'])) {
        http_response_code(401);
    }
    echo json_encode($result);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CAPK 2-1-1</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .login-container {
            max-width: 420px;
            margin: 80px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .login-container h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
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
    </style>
</head>
<body>
<div class="login-container">
    <h1>CAPK 2-1-1 Login</h1>
    <form id="loginForm">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Log In</button>
        <div class="error" id="errorMsg"></div>
    </form>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const data    = new FormData(e.target);
            const errorEl = document.getElementById('errorMsg');
            errorEl.textContent = '';

            const res  = await fetch('/login.php', { method: 'POST', body: data });
            const json = await res.json();

            if (json.error) {
                errorEl.textContent = json.error;
            } else if (json.success) {
                window.location.href = json.redirect;
            }
        });
    </script>
</div>
</body>
</html>

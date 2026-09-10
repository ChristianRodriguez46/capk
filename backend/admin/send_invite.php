<?php
require_once __DIR__ . '/../backend/auth/session.php';
require_once __DIR__ . '/../backend/db.php';

requireAdmin();

// Fetch agencies for the dropdown
$db   = db();
$stmt = $db->prepare("SELECT agency_id, name FROM agencies WHERE is_active = true ORDER BY name ASC");
$stmt->execute();
$agencies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite User — CAPK Admin</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .invite-container {
            max-width: 520px;
            margin: 60px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .invite-container h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .invite-container p.subtitle { color: #666; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.25rem; }
        .form-group input,
        .form-group select {
            width: 100%; padding: 0.6rem 0.8rem;
            border: 1px solid #ccc; border-radius: 4px;
            font-size: 1rem; box-sizing: border-box;
        }
        .role-toggle {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .role-toggle label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .role-toggle input {
            width: auto;
        }
        .role-toggle label.active {
            border-color: #c0392b;
            background: #fdf0f0;
            color: #c0392b;
        }
        #agencyGroup.hidden {
            display: none;
        }
        .btn {
            width: 100%; padding: 0.75rem;
            background: #c0392b; color: #fff;
            border: none; border-radius: 4px;
            font-size: 1rem; cursor: pointer; font-weight: 600;
        }
        .btn:hover { background: #a93226; }
        .back-link { display: inline-block; margin-bottom: 1.5rem; color: #c0392b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
        .result { margin-top: 1.5rem; padding: 1rem; border-radius: 6px; display: none; }
        .result.success { background: #eafaf1; border: 1px solid #27ae60; }
        .result.error   { background: #fdf0f0; border: 1px solid #c0392b; }
        .result p { margin: 0 0 0.5rem; font-weight: 600; }
        .invite-link {
            word-break: break-all;
            background: #f4f4f4;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        .copy-btn {
            margin-top: 0.5rem;
            padding: 0.4rem 1rem;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="invite-container">
    <a href="/admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>Invite User</h1>
    <p class="subtitle">Send a one-time invite link. The link expires in 72 hours.</p>

    <form id="inviteForm">
        <div class="form-group">
            <label>Account Type</label>
            <div class="role-toggle">
                <label id="roleLabelAgency" class="active">
                    <input type="radio" name="role" value="agency" checked>
                    Agency Manager
                </label>
                <label id="roleLabelAdmin">
                    <input type="radio" name="role" value="admin">
                    Admin
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="contact@agency.org" required>
        </div>

        <div class="form-group" id="agencyGroup">
            <label>Agency</label>
            <select name="agency_id" id="agencySelect" required>
                <option value="">— Select an agency —</option>
                <?php foreach ($agencies as $agency): ?>
                    <option value="<?= $agency['agency_id'] ?>">
                        <?= htmlspecialchars($agency['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn">Send Invite</button>
    </form>

    <div class="result" id="result">
        <p id="resultMsg"></p>
        <div class="invite-link" id="inviteLink" style="display:none;"></div>
        <button class="copy-btn" id="copyBtn" style="display:none;" onclick="copyLink()">Copy Link</button>
    </div>
</div>

<script>
    const roleRadios    = document.querySelectorAll('input[name="role"]');
    const agencyGroup   = document.getElementById('agencyGroup');
    const agencySelect  = document.getElementById('agencySelect');
    const labelAgency   = document.getElementById('roleLabelAgency');
    const labelAdmin    = document.getElementById('roleLabelAdmin');

    function updateRoleUI() {
        const role = document.querySelector('input[name="role"]:checked').value;

        if (role === 'admin') {
            agencyGroup.classList.add('hidden');
            agencySelect.required = false;
            labelAdmin.classList.add('active');
            labelAgency.classList.remove('active');
        } else {
            agencyGroup.classList.remove('hidden');
            agencySelect.required = true;
            labelAgency.classList.add('active');
            labelAdmin.classList.remove('active');
        }
    }

    roleRadios.forEach(r => r.addEventListener('change', updateRoleUI));
    updateRoleUI();

    document.getElementById('inviteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data      = new FormData(e.target);
        const resultEl  = document.getElementById('result');
        const msgEl     = document.getElementById('resultMsg');
        const linkEl    = document.getElementById('inviteLink');
        const copyBtn   = document.getElementById('copyBtn');

        resultEl.style.display = 'none';
        resultEl.className     = 'result';

        const res  = await fetch('/api.php?action=send_invite', { method: 'POST', body: data });
        const json = await res.json();

        resultEl.style.display = 'block';

        if (json.error) {
            resultEl.classList.add('error');
            msgEl.textContent = json.error;
            linkEl.style.display = 'none';
            copyBtn.style.display = 'none';
        } else {
            resultEl.classList.add('success');
            msgEl.textContent = json.warning || json.message;

            if (json.invite_link) {
                linkEl.textContent    = json.invite_link;
                linkEl.style.display  = 'block';
                copyBtn.style.display = 'inline-block';
            }
        }
    });

    function copyLink() {
        const link = document.getElementById('inviteLink').textContent;
        navigator.clipboard.writeText(link).then(() => {
            document.getElementById('copyBtn').textContent = 'Copied!';
        });
    }
</script>
</body>
</html>

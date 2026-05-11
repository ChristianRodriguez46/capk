<?php
/**
 * admin_dashboard.php — CAPK Admin Portal Dashboard
 * Food Pantry Directory Management
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPK Admin Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/portal.css">
</head>
<body class="admin-body">

<!-- Top Bar -->
<div class="admin-topbar">
    <div class="admin-topbar__left">
        <strong>CAPK Admin Portal</strong>
        <span class="admin-topbar__subtitle">Food Pantry Directory Management</span>
    </div>
    <div class="admin-topbar__right">
        <a href="/" class="admin-topbar__link">View Public Directory</a>
        <a href="logout.php" class="admin-topbar__btn">Sign Out</a>
    </div>
</div>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__logo">
            <img src="https://www.capk.org/wp-content/themes/capk-new/images/logo.svg" alt="CAPK" width="140">
        </div>
        
        <nav class="admin-nav">
            <a href="admin_dashboard.php" class="admin-nav__item active">
                <span class="icon">📊</span> Dashboard
            </a>
            <a href="agencies.php" class="admin-nav__item">
                <span class="icon">🏢</span> Agencies
            </a>
            <a href="programs.php" class="admin-nav__item">
                <span class="icon">📋</span> Programs
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1 class="admin-header__title">CAPK Admin Dashboard</h1>
            <p class="admin-header__subtitle">Monitor agency updates and directory statistics.</p>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card__icon"></div>
                <div class="stat-card__value">47</div>
                <div class="stat-card__label">Active Agencies</div>
                <div class="stat-card__change positive">+3 this month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon"></div>
                <div class="stat-card__value">23</div>
                <div class="stat-card__label">Updates This Week</div>
                <div class="stat-card__change">From 18 agencies</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon"></div>
                <div class="stat-card__value">152</div>
                <div class="stat-card__label">Total Programs</div>
                <div class="stat-card__change positive">+5 new this month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon"></div>
                <div class="stat-card__value">1,247</div>
                <div class="stat-card__label">Directory Searches</div>
                <div class="stat-card__change">This week</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Most Active Agencies -->
            <div class="card">
                <h3 class="card__title">Most Active Agencies</h3>
                <ul class="activity-list">
                    <li><span>Downtown Community Food Pantry</span><strong> 8 updates</strong></li>
                    <li><span>Eastside Food Bank</span><strong> 6 updates</strong></li>
                    <li><span>North Valley Community Services</span><strong> 5 updates</strong></li>
                </ul>
            </div>

            <!-- Update Breakdown -->
            <div class="card">
                <h3 class="card__title">Update Breakdown</h3>
                <ul class="activity-list">
                    <li><span>Operating Hours</span><strong> 12 updates</strong></li>
                    <li><span>Contact Information</span><strong> 7 updates</strong></li>
                    <li><span>Services Offered</span><strong> 4 updates</strong></li>
                </ul>
            </div>
        </div>

        <!-- Recent Updates -->
        <div class="card recent-updates">
            <h3 class="card__title">Recent Agency Updates</h3>
            <p class="card__subtitle">Latest changes made by food pantries across the directory</p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agency</th>
                        <th>Update Type</th>
                        <th>Changes</th>
                        <th>Updated By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Will be populated by JS/PHP later -->
                    <tr>
                        <td>Downtown Community Food Pantry</td>
                        <td>Operating Hours</td>
                        <td>Updated Tuesday hours</td>
                        <td>John Smith</td>
                        <td>May 4, 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
</script>
</body>
</html>

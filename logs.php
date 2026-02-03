<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Logs | LuminaLib LMS</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=7">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="library-app">
    <?php include 'includes/navbar.php'; ?>

    <main class="app-main">
        <div class="content-container">
            <div class="admin-header-row">
                <div class="admin-title-group">
                    <h1 class="page-title">System Logs</h1>
                    <p class="page-subtitle">Monitor user activities and system events.</p>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <div id="toast" class="toast"></div>
    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loadLogs = async () => {
                const res = await fetch('api/get_logs.php');
                const data = await res.json();
                if (data.success) {
                    const tbody = document.getElementById('logsTableBody');
                    tbody.innerHTML = data.logs.map(l => `
                        <tr>
                            <td style="font-family: monospace; font-size: 0.8125rem;">${new Date(l.created_at).toLocaleString()}</td>
                            <td style="font-weight: 600;">${l.username}</td>
                            <td><span class="status-tag" style="text-transform: none; background: #eee; color: #333;">${l.action}</span></td>
                            <td style="color: var(--text-muted);">${l.ip_address}</td>
                        </tr>
                    `).join('');
                }
            };
            loadLogs();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

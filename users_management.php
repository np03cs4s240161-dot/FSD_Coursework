<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | LuminaLib</title>
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
                    <h1 class="page-title">Users Management</h1>
                    <p class="page-subtitle">View and manage system users.</p>
                </div>

                <div class="admin-actions-group">
                    <div class="main-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="globalSearch" placeholder="Search users...">
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
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
            const usersTableBody = document.getElementById('usersTableBody');
            
            const loadUsers = async (keyword = '') => {
                const res = await fetch(`api/get_users.php?keyword=${keyword}`);
                const data = await res.json();
                if (data.success) {
                    usersTableBody.innerHTML = data.users.map(u => `
                        <tr>
                            <td>${u.id}</td>
                            <td style="font-weight: 600;">${u.first_name || ''} ${u.last_name || ''}</td>
                            <td>${u.username}</td>
                            <td>${u.email || 'N/A'}</td>
                            <td><span class="badge role-${(u.role_name || 'patron').toLowerCase()}">${u.role_name}</span></td>
                            <td style="color: var(--text-muted); font-size: 0.875rem;">${new Date(u.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn delete-user" data-id="${u.id}" title="Delete User">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </td>
                        </tr>
                    `).join('');

                    document.querySelectorAll('.delete-user').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('Delete this user?')) return;
                            const fd = new FormData();
                            fd.append('id', btn.dataset.id);
                            const res = await fetch('api/delete_user.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                showToast(data.message);
                                loadUsers();
                            } else {
                                showToast(data.message, 'error');
                            }
                        });
                    });
                }
            };

            let searchTimeout;
            const globalSearch = document.getElementById('globalSearch');
            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    const keyword = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadUsers(keyword);
                    }, 300);
                });
            }

            loadUsers();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

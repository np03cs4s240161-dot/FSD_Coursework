<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    logout(); // Invalid session
}

// Initialize variables for navbar
$user_role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? $user['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | LuminaLib</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=12">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .settings-nav {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            position: sticky;
            top: 6rem;
        }

        .settings-nav-item {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--text-main);
            text-decoration: none;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
            cursor: pointer;
        }

        .settings-nav-item:last-child {
            border-bottom: none;
        }

        .settings-nav-item:hover, .settings-nav-item.active {
            background: var(--sidebar-bg);
            border-left: 3px solid var(--accent);
            padding-left: calc(1.5rem - 3px);
            font-weight: 500;
        }

        .settings-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .settings-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .settings-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--text-main);
        }

        .settings-header p {
            margin: 0.5rem 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            .settings-nav {
                position: static;
                display: flex;
                overflow-x: auto;
                margin-bottom: 1.5rem;
            }
            .settings-nav-item {
                border-bottom: none;
                border-right: 1px solid var(--border);
                white-space: nowrap;
            }
        }
    </style>
</head>
<body class="library-app">
    <?php include 'includes/navbar.php'; ?>

    <main class="app-main">
        <div class="content-container" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
            <div style="margin-bottom: 2rem;">
                <h1 class="page-title" style="margin: 0;">Settings</h1>
                <p style="color: var(--text-muted);">Manage your account preferences and security.</p>
            </div>

            <div class="settings-grid">
                <!-- settings Navigation -->
                <div class="settings-nav">
                    <div class="settings-nav-item active" data-target="profile">Profile Information</div>
                    <div class="settings-nav-item" data-target="security">Security</div>
                    <!-- <div class="settings-nav-item" data-target="preferences">Preferences</div> -->
                </div>

                <!-- Settings Content -->
                <div class="settings-content">
                    
                    <!-- Profile Section -->
                    <div id="profileSection" class="settings-card">
                        <div class="settings-header">
                            <h2>Profile Information</h2>
                            <p>Update your personal details and contact info.</p>
                        </div>
                        <form id="profileForm">
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                                <small style="color: var(--text-muted);">Used for login and display.</small>
                            </div>

                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 2rem; text-align: right;">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Section -->
                    <div id="securitySection" class="settings-card" style="display: none;">
                        <div class="settings-header">
                            <h2>Security</h2>
                            <p>Manage your password and account security.</p>
                        </div>
                        <form id="securityForm">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required placeholder="••••••••">
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required placeholder="••••••••" minlength="6">
                            </div>

                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required placeholder="••••••••" minlength="6">
                            </div>

                            <div class="form-actions" style="margin-top: 2rem; text-align: right;">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <!-- Include Shared JS -->
    <script src="assets/js/app.js?v=12"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tab Switching
            const tabs = document.querySelectorAll('.settings-nav-item');
            const sections = {
                'profile': document.getElementById('profileSection'),
                'security': document.getElementById('securitySection')
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.target;
                    
                    // Update tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    // Update sections
                    Object.values(sections).forEach(s => s.style.display = 'none');
                    sections[target].style.display = 'block';
                    // Animation
                    sections[target].style.animation = 'fadeIn 0.3s ease';
                });
            });

            // Re-toast function if managed by app.js differently or use window.showToast if verified
            // app.js has showToast but it might not be global in scope depending on definition. 
            // app.js defines const showToast inside DOMContentLoaded. Let's define a local helper just in case 
            // or rely on the one I'll attach to window in previous steps (I did window.showToast in dashboard.php updates, but maybe not app.js)
            // I'll define one here to be safe
            const showToast = (msg, type='success') => {
                const toast = document.getElementById('toast');
                toast.textContent = msg;
                toast.className = `toast ${type} show`;
                setTimeout(() => toast.classList.remove('show'), 3000);
            };

            // Profile Form Handler
            document.getElementById('profileForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                const originalText = btn.textContent;
                btn.textContent = 'Saving...';
                btn.disabled = true;

                const fd = new FormData(e.target);

                try {
                    const res = await fetch('api/update_profile.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    
                    if (data.success) {
                        showToast(data.message);
                        // Update displayed name in navbar if username/name changed (optional, would require reload or direct DOM update)
                        // Simple way: 
                        const navUser = document.querySelector('.user-name');
                        const navDropdownName = document.querySelector('.dropdown-user-name');
                        if(fd.get('username')) {
                             if(navUser) navUser.textContent = fd.get('username');
                             if(navDropdownName) navDropdownName.textContent = fd.get('username');
                        }
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (err) {
                    showToast('An error occurred.', 'error');
                } finally {
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            });

            // Security Form Handler
            document.getElementById('securityForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const currentPass = e.target.current_password.value;
                const newPass = e.target.new_password.value;
                const confirmPass = e.target.confirm_password.value;

                if (newPass !== confirmPass) {
                    showToast('New passwords do not match.', 'error');
                    return;
                }

                const btn = e.target.querySelector('button[type="submit"]');
                btn.textContent = 'Updating...';
                btn.disabled = true;

                const fd = new FormData(e.target);

                try {
                    const res = await fetch('api/change_password.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    
                    if (data.success) {
                        showToast(data.message);
                        e.target.reset();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (err) {
                    showToast('An error occurred.', 'error');
                } finally {
                    btn.textContent = 'Update Password';
                    btn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>

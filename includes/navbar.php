<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="app-header">
    <div class="header-container">
        <div class="header-left">
            <a href="dashboard.php" class="brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="brand-icon"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                <span class="brand-text">LuminaLib</span>
            </a>
        </div>

        <nav class="header-nav">
            <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            
            <?php if ($user_role === 'Patron'): ?>
                <a href="book_list.php" class="nav-link <?= $current_page == 'book_list.php' ? 'active' : '' ?>">Book List</a>
                <a href="my_bookings.php" class="nav-link <?= $current_page == 'my_bookings.php' ? 'active' : '' ?>">My Bookings</a>
            <?php endif; ?>

            <?php if ($user_role === 'Admin'): ?>
                <a href="categories.php" class="nav-link <?= $current_page == 'categories.php' ? 'active' : '' ?>">Categories</a>
                <a href="bookings_management.php" class="nav-link <?= $current_page == 'bookings_management.php' ? 'active' : '' ?>">Bookings</a>
                <a href="users_management.php" class="nav-link <?= $current_page == 'users_management.php' ? 'active' : '' ?>">Users</a>
                <a href="logs.php" class="nav-link <?= $current_page == 'logs.php' ? 'active' : '' ?>">Logs</a>
            <?php endif; ?>
        </nav>

        <div class="header-right">
            <div class="header-actions">
                <button id="themeToggle" class="header-btn" title="Toggle Theme"></button>
                <button id="mobileMenuBtn" class="header-btn mobile-menu-btn" title="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>

            <div class="user-profile-wrapper" style="position: relative;">
                <div class="user-profile" id="profileTrigger">
                    <div class="user-avatar">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($username) ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-header">
                        <span class="dropdown-user-name"><?= htmlspecialchars($username) ?></span>
                        <span class="dropdown-user-role"><?= htmlspecialchars($user_role) ?></span>
                    </div>
                    <a href="settings.php" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg> 
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> 
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

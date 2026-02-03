<?php 
$current_page = basename($_SERVER['PHP_SELF']);
$is_auth = ($current_page === 'login.php' || $current_page === 'register.php');
?>
<nav class="landing-nav <?= $is_auth ? 'scrolled' : '' ?>">
    <div class="landing-nav-container">
        <a href="index.php" class="landing-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="brand-icon"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            <span class="brand-text">LuminaLib</span>
        </a>

        <div class="landing-nav-right">
            <button id="themeToggle" class="theme-toggle" title="Toggle Theme"></button>
            <button id="mobileMenuBtn" class="mobile-menu-btn" title="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            
            <div class="nav-actions landing-nav-links">
                <?php 
                if ($current_page !== 'login.php'): ?>
                    <a href="login.php" class="btn btn-secondary btn-pill">Sign In</a>
                <?php endif; ?>
                
                <?php if ($current_page !== 'register.php'): ?>
                    <a href="register.php" class="btn btn-primary btn-pill">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuminaLib — Library Management System</title>
    <meta name="description" content="LuminaLib is a modern library management system designed for organizing, tracking, and managing your book collection with ease.">
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="landing-page">
    <!-- Navigation -->
    <?php include 'includes/landing_navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <div class="hero-glow hero-glow-1"></div>
            <div class="hero-glow hero-glow-2"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <span>Library Management System</span>
            </div>
            <h1 class="hero-title">
                <span class="hero-title-line">Library</span>
                <span class="hero-title-accent">Reference Tool</span>
            </h1>
            <p class="hero-subtitle">
                A modern space for organizing and managing your book collection. 
                Track books, authors, categories, and borrowing history with elegance.
            </p>
            <div class="hero-cta">
                <a href="login.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="#features" class="btn btn-secondary btn-lg">Explore Features</a>
            </div>
            <?php
                // Fetch a few books for the mockup
                $stmt = $pdo->query("SELECT title FROM books LIMIT 3");
                $mockupBooks = $stmt->fetchAll();
                while(count($mockupBooks) < 3) {
                    $mockupBooks[] = ['title' => 'Sample Book'];
                }
            ?>
        </div>
        <div class="hero-visual">
            <!-- Reuse the markup from preview.html without heavy modification -->
            <div class="hero-mockup">
                <div class="mockup-header">
                    <div class="mockup-dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="mockup-title">Dashboard</span>
                </div>
                <div class="mockup-content">
                    <div class="mockup-sidebar">
                        <div class="mockup-menu-item active"></div>
                        <div class="mockup-menu-item"></div>
                        <div class="mockup-menu-item"></div>
                        <div class="mockup-menu-item" style="margin-top: auto;"></div>
                    </div>
                    <div class="mockup-main">
                        <div style="height: 20px; width: 120px; background: var(--border); border-radius: 4px; margin-bottom: 1.5rem;"></div>
                        <div class="mockup-cards">
                            <div class="mockup-card" style="background: #1A1A1A; display: flex; align-items: center; justify-content: center; color: #FFD700; font-style: italic; font-size: 0.6rem; padding: 0.5rem; text-align: center;"><?= htmlspecialchars($mockupBooks[0]['title']) ?></div>
                            <div class="mockup-card" style="background: #FAF8F5; display: flex; align-items: center; justify-content: center; color: #1A1A1A; font-size: 0.6rem; padding: 0.5rem; text-align: center;"><?= htmlspecialchars($mockupBooks[1]['title']) ?></div>
                            <div class="mockup-card" style="background: #FF6B6B; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.6rem; padding: 0.5rem; text-align: center;"><?= htmlspecialchars($mockupBooks[2]['title']) ?></div>
                        </div>
                        <div class="mockup-table">
                            <?php foreach ($mockupBooks as $mb): ?>
                                <div class="mockup-row" style="display: flex; align-items: center; padding: 0 0.5rem; color: var(--text-muted); font-size: 0.6rem; overflow: hidden; white-space: nowrap;"><?= htmlspecialchars($mb['title']) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2 class="section-title">Everything you need to manage your library</h2>
                <p class="section-subtitle">Powerful tools designed for librarians and book enthusiasts</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    </div>
                    <h3>Book Catalog</h3>
                    <p>Organize your entire collection with detailed metadata, ISBNs, and publication info.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4L18.5 2.5z"></path></svg>
                    </div>
                    <h3>Author Management</h3>
                    <p>Track authors, their works, and biographies in one centralized location.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h3>Categories & Genres</h3>
                    <p>Flexible categorization system to organize books by genre, subject, or custom tags.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    </div>
                    <h3>Dark & Light Mode</h3>
                    <p>Beautiful interface that adapts to your preference with stunning themes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="stats-section">
        <div class="section-container" style="text-align: center; padding: 4rem 0;">
            <h2 class="section-title" style="margin-bottom: 2rem;">Ready to organize your library?</h2>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="section-container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="brand-icon">📚</span>
                    <span>LuminaLib</span>
                </div>
                <p class="footer-text">A modern library management system built with ❤️</p>
                <p class="footer-copyright">© <?= date('Y') ?> LuminaLib. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        // Landing Page Specific JS
        const landingNav = document.querySelector('.landing-nav');
        if (landingNav) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    landingNav.classList.add('scrolled');
                } else {
                    landingNav.classList.remove('scrolled');
                }
            });
        }
    </script>
</body>
</html>

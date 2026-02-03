<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.username = ? OR u.email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'] ?? 'Patron'; // Fallback to Patron if role missing
            redirect('dashboard.php');
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LuminaLib</title>
    <meta name="description" content="Sign in to LuminaLib - Your modern library management system">
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <?php include 'includes/landing_navbar.php'; ?>
    <div class="auth-container">
        <!-- Left Side - Image -->
        <div class="auth-image">
            <!-- You can replace this with your own image -->
            <img src="assets/images/login-bg.jpg" alt="Login" onerror="this.style.display='none'">
            <div class="image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="placeholder-icon" style="margin-bottom: 1rem;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                <span class="placeholder-text">LuminaLib</span>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h1 class="auth-title">Welcome back</h1>

                <?php if ($error): ?>
                    <div class="auth-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="auth-form">
                    <div class="form-field">
                        <label for="username">Email or User name:</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Youremail@gmail.com"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Your password"
                            required
                            autocomplete="current-password"
                        >
                        <div class="form-actions">
                            <a href="index.php" class="forgot-link">← Back to Home</a>
                            <a href="#" class="forgot-link">Forgot your password?</a>
                        </div>
                    </div>

                    <div class="form-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me on this device</label>
                    </div>

                    <button type="submit" class="btn-auth-primary">Log in</button>
                </form>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="btn-social">
                        <span class="social-icon">G</span>
                        Continue with Google
                    </button>
                    <button type="button" class="btn-social">
                        <span class="social-icon"></span>
                        Continue with Apple
                    </button>
                    <button type="button" class="btn-social" onclick="location.href='index.php'">
                        Continue as a guest
                    </button>
                </div>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>

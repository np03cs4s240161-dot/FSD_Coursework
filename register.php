<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? ''); // New field
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (strlen($first_name) < 2 || strlen($last_name) < 2) {
        $error = "Name must be at least 2 characters.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            // Insert new user (default role_id = 2 for regular user)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Note: We need to update the database schema to support first_name, last_name, phone
            // For now, I will modify the query assuming columns will be added
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, phone, password_hash, role_id) VALUES (?, ?, ?, ?, ?, ?, 2)");
            
            try {
                if ($stmt->execute([$first_name, $last_name, $username, $email, $phone, $password_hash])) {
                    $success = "Account created successfully! You can now log in.";
                } else {
                    $error = "An error occurred. Please try again.";
                }
            } catch (PDOException $e) {
                // Determine if error is due to missing columns
                if (strpos($e->getMessage(), 'Unknown column') !== false) {
                     // Fallback for current schema (just to prevent crash if DB update fails)
                     $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, 2)");
                     if ($stmt->execute([$username, $email, $password_hash])) {
                        $success = "Account created successfully! (Note: Extra profile details were not saved due to database pending update).";
                     }
                } else {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | LuminaLib</title>
    <meta name="description" content="Create an account on LuminaLib - Your modern library management system">
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <?php include 'includes/landing_navbar.php'; ?>
    <div class="auth-container">
        <!-- Left Side - Image -->
        <div class="auth-image">
            <img src="assets/images/signup-bg.jpg" alt="Sign Up" onerror="this.style.display='none'">
            <div class="image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="placeholder-icon" style="margin-bottom: 1rem;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                <span class="placeholder-text">LuminaLib</span>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h1 class="auth-title">Create account</h1>
                <p class="auth-subtitle">Join LuminaLib and start managing your library</p>

                <?php if ($error): ?>
                    <div class="auth-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="auth-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="auth-form">
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                        </div>
                        <div class="form-field">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name"  required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"  required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>

                    <div class="form-field">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="Your phone number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <div class="form-field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Youremail@example.com" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                        </div>
                        <div class="form-field">
                            <label for="confirm_password">Confirm</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a></label>
                    </div>

                    <div class="form-actions" style="justify-content: flex-start; margin-bottom: 1rem;">
                        <a href="index.php" class="forgot-link">← Back to Home</a>
                    </div>
                    <button type="submit" class="btn-auth-primary">Sign up</button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Log in</a>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>

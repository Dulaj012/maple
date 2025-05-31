<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        if (loginUser($email, $password)) {
            // Redirect based on role
            if (isAdmin()) {
                header('Location: admin/index.php');
            } else {
                // Redirect to intended page or dashboard
                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'dashboard.php';
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MapleCart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-card">
                <div class="auth-logo">
                    <h2>Maple<span>Cart</span></h2>
                </div>
                <h3>Login to Your Account</h3>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-auth">Login</button>
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Register</a></p>
                        <p><a href="forgot-password.php">Forgot Password?</a></p>
                    </div>
                    <div class="social-login">
                        <div class="divider"><span>or login with</span></div>
                        <div class="social-buttons">
                            <a href="#" class="social-button google"><i class="fab fa-google"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
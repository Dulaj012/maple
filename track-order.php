<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = clean($_POST['order_number']);
    $email = clean($_POST['email']);
    
    $order = getOrderByNumber($orderNumber);
    
    if (!$order || $order['shipping_email'] !== $email) {
        $error = 'Invalid order number or email address.';
    } else {
        $_SESSION['guest_email'] = $email;
        header('Location: order-detail.php?id=' . $order['id']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - MapleCart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Track Order Section -->
    <section class="track-order-section">
        <div class="container">
            <div class="track-order-card">
                <h2>Track Your Order</h2>
                <p>Enter your order number and email address to track your order.</p>

                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="track-order.php" method="post">
                    <div class="form-group">
                        <label for="order_number">Order Number</label>
                        <input type="text" class="form-control" id="order_number" name="order_number" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Track Order</button>
                </form>

                <div class="track-order-footer">
                    <p>Don't have an account? <a href="register.php">Register</a> to manage your orders easily.</p>
                </div>
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
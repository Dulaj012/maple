<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = 'order-detail.php';
    header('Location: login.php');
    exit;
}

// Get order ID
if (!isset($_GET['id'])) {
    header('Location: dashboard.php?tab=orders');
    exit;
}

$orderId = (int)$_GET['id'];
$order = getOrderById($orderId);
$orderItems = getOrderItems($orderId);

// Verify order belongs to user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: dashboard.php?tab=orders');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - MapleCart</title>
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

    <!-- Order Detail Section -->
    <section class="dashboard-section">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-user">
                            <div class="avatar">
                                <img src="https://via.placeholder.com/100x100" alt="User Avatar">
                            </div>
                            <h4><?php echo $_SESSION['user_name']; ?></h4>
                            <p><?php echo $_SESSION['user_email']; ?></p>
                        </div>
                        <div class="dashboard-menu">
                            <ul>
                                <li><a href="dashboard.php?tab=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <li><a href="dashboard.php?tab=orders" class="active"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                                <li><a href="dashboard.php?tab=wishlist"><i class="fas fa-heart"></i> Wishlist</a></li>
                                <li><a href="dashboard.php?tab=profile"><i class="fas fa-user"></i> Profile</a></li>
                                <li><a href="dashboard.php?tab=change-password"><i class="fas fa-lock"></i> Change Password</a></li>
                                <li class="logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-lg-9">
                    <div class="dashboard-content">
                        <div class="content-title">
                            <h3>Order #<?php echo $order['order_number']; ?></h3>
                            <a href="dashboard.php?tab=orders" class="btn btn-outline-primary btn-sm">Back to Orders</a>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Order Status</h5>
                                    <span class="order-status <?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                                        <p><strong>Shipping Method:</strong> Standard Shipping</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($orderItems as $item): ?>
                                            <tr>
                                                <td><?php echo $item['name']; ?></td>
                                                <td><?php echo formatPrice($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatPrice($item['total']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            </tr>
                                            <?php if (isset($order['discount']) && $order['discount'] > 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                                <td>-<?php echo formatPrice($order['discount']); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                                <td>Free</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td><strong><?php echo formatPrice($order['total_amount'] - ($order['discount'] ?? 0)); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Shipping Address</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><?php echo $order['shipping_name']; ?></p>
                                        <p class="mb-1"><?php echo $order['shipping_address']; ?></p>
                                        <p class="mb-1"><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zipcode']; ?></p>
                                        <p class="mb-1"><?php echo $order['shipping_country']; ?></p>
                                        <p class="mb-0">Phone: <?php echo $order['shipping_phone']; ?></p>
                                    </div>
                                </div>
                            </div>

                            <?php if ($order['notes']): ?>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Order Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0"><?php echo $order['notes']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
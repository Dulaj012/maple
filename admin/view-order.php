<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get order ID
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['id'];
$order = getOrderById($orderId);
$orderItems = getOrderItems($orderId);

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Handle payment status update
if (isset($_POST['update_payment_status'])) {
    $status = clean($_POST['payment_status']);
    
    $sql = "UPDATE orders SET payment_status = '$status' WHERE id = $orderId";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Payment status updated successfully.';
        $order['payment_status'] = $status; // Update status for display
        
        // Send email notification to customer
        $subject = "Payment Status Update - Order #" . $order['order_number'];
        $message = "<p>Dear " . $order['shipping_name'] . ",</p>";
        $message .= "<p>The payment status for your order #" . $order['order_number'] . " has been updated to: <strong>" . ucfirst($status) . "</strong></p>";
        
        if ($status === 'accepted') {
            $message .= "<p>Thank you for your payment. Your order will be processed shortly.</p>";
        } elseif ($status === 'rejected') {
            $message .= "<p>Unfortunately, we could not verify your payment. Please contact us for assistance.</p>";
        }
        
        $message .= "<p>Best regards,<br>MapleCart Team</p>";
        
        sendEmail($order['shipping_email'], $subject, $message);
    } else {
        $_SESSION['error_message'] = 'Failed to update payment status.';
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $status = clean($_POST['status']);
    
    if (updateOrderStatus($orderId, $status)) {
        $_SESSION['success_message'] = 'Order status updated successfully.';
        $order['status'] = $status; // Update status for display
    } else {
        $_SESSION['error_message'] = 'Failed to update order status.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-dashboard">
    <!-- Admin Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Admin Content -->
    <div class="admin-content">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Admin Main -->
        <div class="admin-main">
            <div class="container-fluid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h4>Order #<?php echo $order['order_number']; ?></h4>
                        <a href="orders.php" class="btn btn-outline-primary">Back to Orders</a>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Order Items -->
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
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong><?php echo formatPrice($order['total_amount'] - ($order['discount'] ?? 0)); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <?php if ($order['notes']): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Notes</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0"><?php echo $order['notes']; ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <!-- Order Status -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Status</h5>
                                </div>
                                <div class="card-body">
                                    <form action="view-order.php?id=<?php echo $orderId; ?>" method="post">
                                        <input type="hidden" name="update_status" value="1">
                                        <div class="mb-3">
                                            <select name="status" class="form-select">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Payment Status</h5>
                                </div>
                                <div class="card-body">
                                    <form action="view-order.php?id=<?php echo $orderId; ?>" method="post">
                                        <input type="hidden" name="update_payment_status" value="1">
                                        <div class="mb-3">
                                            <select name="payment_status" class="form-select">
                                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="accepted" <?php echo $order['payment_status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                                <option value="rejected" <?php echo $order['payment_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Update Payment Status</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?php echo $order['shipping_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $order['shipping_email']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $order['shipping_phone']; ?></p>
                                </div>
                            </div>

                            <!-- Shipping Address -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Shipping Address</h5>
                                </div>
                                <div class="card-body">
                                    <p><?php echo $order['shipping_address']; ?></p>
                                    <p><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zipcode']; ?></p>
                                    <p><?php echo $order['shipping_country']; ?></p>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Payment Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge <?php 
                                            echo $order['payment_status'] === 'accepted' ? 'bg-success' : 
                                                ($order['payment_status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); 
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
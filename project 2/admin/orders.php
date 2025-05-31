<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle order deletion
if (isset($_GET['delete'])) {
    $orderId = (int)$_GET['delete'];
    
    // Delete order items first
    $sql = "DELETE FROM order_items WHERE order_id = $orderId";
    $conn->query($sql);
    
    // Then delete the order
    $sql = "DELETE FROM orders WHERE id = $orderId";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Order deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to delete order.';
    }
    header('Location: orders.php');
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = clean($_POST['status']);
    
    if (updateOrderStatus($orderId, $status)) {
        $_SESSION['success_message'] = 'Order status updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to update order status.';
    }
    header('Location: orders.php');
    exit;
}

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$orders = getAdminOrders($limit, $offset);
$totalOrders = getTotalOrders();
$totalPages = ceil($totalOrders / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard</title>
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
                        <h4>Orders</h4>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td class="order-id">#<?php echo $order['order_number']; ?></td>
                                    <td>
                                        <?php if ($order['user_name']): ?>
                                            <?php echo $order['user_name']; ?><br>
                                            <small class="text-muted"><?php echo $order['user_email']; ?></small>
                                        <?php else: ?>
                                            <?php echo $order['shipping_name']; ?><br>
                                            <small class="text-muted"><?php echo $order['shipping_email']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <form action="orders.php" method="post" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" class="form-select status-select" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="view-order.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">View</a>
                                        <a href="orders.php?delete=<?php echo $order['id']; ?>" class="action-btn delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="admin-pagination">
                        <?php echo generatePagination($page, $totalPages, 'orders.php?'); ?>
                    </div>
                    <?php endif; ?>
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
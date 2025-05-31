<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get customer ID
if (!isset($_GET['id'])) {
    header('Location: customers.php');
    exit;
}

$customerId = (int)$_GET['id'];
$customer = getUserById($customerId);

if (!$customer || $customer['role'] !== 'customer') {
    header('Location: customers.php');
    exit;
}

// Get customer's purchase history
$purchaseHistory = getCustomerPurchaseHistory($customerId);

// Get customer's orders
$orders = getUserOrders($customerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - Admin Dashboard</title>
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
                        <h4>Customer Details</h4>
                        <a href="customers.php" class="btn btn-outline-primary">Back to Customers</a>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <!-- Customer Info -->
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <img src="../assets/images/placeholder.png" alt="<?php echo $customer['name']; ?>" 
                                         class="rounded-circle mb-3" width="100" height="100">
                                    <h5 class="mb-1"><?php echo $customer['name']; ?></h5>
                                    <p class="text-muted mb-3"><?php echo $customer['email']; ?></p>
                                    <div class="d-grid gap-2">
                                        <a href="mailto:<?php echo $customer['email']; ?>" class="btn btn-primary">
                                            <i class="fas fa-envelope"></i> Send Email
                                        </a>
                                        <a href="orders.php?customer=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-shopping-bag"></i> View Orders
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row text-center">
                                        <div class="col">
                                            <h6 class="mb-1"><?php echo $purchaseHistory['order_count']; ?></h6>
                                            <small class="text-muted">Orders</small>
                                        </div>
                                        <div class="col">
                                            <h6 class="mb-1"><?php echo formatPrice($purchaseHistory['total_spent']); ?></h6>
                                            <small class="text-muted">Total Spent</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Phone:</strong> <?php echo $customer['phone'] ?: 'N/A'; ?></p>
                                    <p><strong>Address:</strong> <?php echo $customer['address'] ?: 'N/A'; ?></p>
                                    <p><strong>City:</strong> <?php echo $customer['city'] ?: 'N/A'; ?></p>
                                    <p><strong>State:</strong> <?php echo $customer['state'] ?: 'N/A'; ?></p>
                                    <p><strong>Zipcode:</strong> <?php echo $customer['zipcode'] ?: 'N/A'; ?></p>
                                    <p><strong>Country:</strong> <?php echo $customer['country'] ?: 'N/A'; ?></p>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Account Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Account Status:</strong> 
                                        <span class="badge <?php echo $customer['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($customer['created_at'])); ?></p>
                                    <p><strong>Last Updated:</strong> <?php echo date('F j, Y', strtotime($customer['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <!-- Recent Orders -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Orders</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($orders) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['order_number']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $order['status']; ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                                    <td>
                                                        <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <p class="text-center mb-0">No orders found.</p>
                                    <?php endif; ?>
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
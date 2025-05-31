<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle coupon deletion
if (isset($_GET['delete'])) {
    $couponId = (int)$_GET['delete'];
    if (deleteCoupon($couponId)) {
        $_SESSION['success_message'] = 'Coupon deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to delete coupon.';
    }
    header('Location: coupons.php');
    exit;
}

// Get all coupons
$coupons = getCoupons();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons - Admin Dashboard</title>
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
                        <h4>Coupons</h4>
                        <a href="add-coupon.php" class="btn btn-primary">Add New Coupon</a>
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
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Min Spend</th>
                                    <th>Max Discount</th>
                                    <th>Usage / Limit</th>
                                    <th>Valid Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($coupons as $coupon): ?>
                                <tr>
                                    <td><code><?php echo $coupon['code']; ?></code></td>
                                    <td><?php echo ucfirst($coupon['type']); ?></td>
                                    <td>
                                        <?php if($coupon['type'] === 'percentage'): ?>
                                            <?php echo $coupon['value']; ?>%
                                        <?php else: ?>
                                            <?php echo formatPrice($coupon['value']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $coupon['min_spend'] ? formatPrice($coupon['min_spend']) : 'No minimum'; ?>
                                    </td>
                                    <td>
                                        <?php echo $coupon['max_discount'] ? formatPrice($coupon['max_discount']) : 'No limit'; ?>
                                    </td>
                                    <td>
                                        <?php if($coupon['usage_limit']): ?>
                                            <?php echo $coupon['usage_count']; ?> / <?php echo $coupon['usage_limit']; ?>
                                        <?php else: ?>
                                            <?php echo $coupon['usage_count']; ?> / ∞
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($coupon['start_date'] && $coupon['end_date']): ?>
                                            <?php echo date('M d, Y', strtotime($coupon['start_date'])); ?> -<br>
                                            <?php echo date('M d, Y', strtotime($coupon['end_date'])); ?>
                                        <?php else: ?>
                                            No expiry
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $coupon['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($coupon['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit-coupon.php?id=<?php echo $coupon['id']; ?>" class="action-btn edit-btn">Edit</a>
                                        <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="action-btn delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this coupon?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
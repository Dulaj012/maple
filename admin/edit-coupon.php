<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get coupon ID
if (!isset($_GET['id'])) {
    header('Location: coupons.php');
    exit;
}

$couponId = (int)$_GET['id'];
$coupon = getCouponById($couponId);

if (!$coupon) {
    header('Location: coupons.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'code' => strtoupper(clean($_POST['code'])),
        'type' => clean($_POST['type']),
        'value' => (float)$_POST['value'],
        'min_spend' => !empty($_POST['min_spend']) ? (float)$_POST['min_spend'] : null,
        'max_discount' => !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null,
        'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        'usage_limit' => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
        'status' => $_POST['status']
    ];

    if (updateCoupon($couponId, $data)) {
        $_SESSION['success_message'] = 'Coupon updated successfully.';
        header('Location: coupons.php');
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to update coupon. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Coupon - Admin Dashboard</title>
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
                        <h4>Edit Coupon</h4>
                        <a href="coupons.php" class="btn btn-outline-primary">Back to Coupons</a>
                    </div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="edit-coupon.php?id=<?php echo $couponId; ?>" method="post" class="admin-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Coupon Code</label>
                                    <input type="text" class="form-control" id="code" name="code" value="<?php echo $coupon['code']; ?>" required>
                                    <small class="text-muted">Enter a unique code (e.g., SUMMER2025)</small>
                                </div>

                                <div class="form-group">
                                    <label for="type">Discount Type</label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="percentage" <?php echo $coupon['type'] === 'percentage' ? 'selected' : ''; ?>>Percentage Discount</option>
                                        <option value="fixed" <?php echo $coupon['type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount Discount</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="value">Discount Value</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="value" name="value" step="0.01" value="<?php echo $coupon['value']; ?>" required>
                                        <span class="input-group-text" id="value-addon"><?php echo $coupon['type'] === 'percentage' ? '%' : '$'; ?></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="min_spend">Minimum Spend (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="min_spend" name="min_spend" step="0.01" value="<?php echo $coupon['min_spend']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_discount">Maximum Discount Amount (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="max_discount" name="max_discount" step="0.01" value="<?php echo $coupon['max_discount']; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="usage_limit">Usage Limit (Optional)</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="<?php echo $coupon['usage_limit']; ?>">
                                    <small class="text-muted">Current usage: <?php echo $coupon['usage_count']; ?></small>
                                </div>

                                <div class="form-group">
                                    <label for="start_date">Start Date (Optional)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $coupon['start_date']; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="end_date">End Date (Optional)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $coupon['end_date']; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" <?php echo $coupon['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $coupon['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-save">Update Coupon</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Update value addon based on discount type
        document.getElementById('type').addEventListener('change', function() {
            const valueAddon = document.getElementById('value-addon');
            valueAddon.textContent = this.value === 'percentage' ? '%' : '$';
        });

        // Validate dates
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date').value;
            if (startDate && this.value < startDate) {
                alert('End date must be after start date');
                this.value = '';
            }
        });
    </script>
</body>
</html>
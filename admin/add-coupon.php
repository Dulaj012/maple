<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
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
        'usage_limit' => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null
    ];

    if (addCoupon($data)) {
        $_SESSION['success_message'] = 'Coupon added successfully.';
        header('Location: coupons.php');
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to add coupon. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Coupon - Admin Dashboard</title>
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
                        <h4>Add New Coupon</h4>
                        <a href="coupons.php" class="btn btn-outline-primary">Back to Coupons</a>
                    </div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="add-coupon.php" method="post" class="admin-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Coupon Code</label>
                                    <input type="text" class="form-control" id="code" name="code" required>
                                    <small class="text-muted">Enter a unique code (e.g., SUMMER2025)</small>
                                </div>

                                <div class="form-group">
                                    <label for="type">Discount Type</label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="percentage">Percentage Discount</option>
                                        <option value="fixed">Fixed Amount Discount</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="value">Discount Value</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="value" name="value" step="0.01" required>
                                        <span class="input-group-text" id="value-addon">%</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="min_spend">Minimum Spend (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="min_spend" name="min_spend" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_discount">Maximum Discount Amount (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="max_discount" name="max_discount" step="0.01">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="usage_limit">Usage Limit (Optional)</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1">
                                    <small class="text-muted">Leave empty for unlimited usage</small>
                                </div>

                                <div class="form-group">
                                    <label for="start_date">Start Date (Optional)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>

                                <div class="form-group">
                                    <label for="end_date">End Date (Optional)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-save">Add Coupon</button>
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
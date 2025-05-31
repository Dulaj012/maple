<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle bestseller status update
if (isset($_POST['update_bestseller'])) {
    $productId = (int)$_POST['product_id'];
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    
    $sql = "UPDATE products SET is_featured = $isFeatured WHERE id = $productId";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Product bestseller status updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to update bestseller status.';
    }
    header('Location: bestsellers.php');
    exit;
}

// Get all products for potential bestsellers
$allProducts = getProducts();

// Get current bestsellers
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get sales data for the selected period
$sql = "SELECT 
    p.*, 
    c.name as category_name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.total) as total_sales
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN '$startDate' AND '$endDate 23:59:59'
    AND o.status != 'cancelled'
GROUP BY p.id
ORDER BY total_quantity DESC";

$result = $conn->query($sql);
$topSellingProducts = [];
while ($row = $result->fetch_assoc()) {
    $topSellingProducts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Selling Products - Admin Dashboard</title>
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
                <!-- Add New Bestseller -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h4>Add New Bestseller</h4>
                    </div>
                    <div class="card-body">
                        <form action="bestsellers.php" method="post" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="product_id" class="form-label">Select Product</label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">Choose a product...</option>
                                    <?php foreach($allProducts as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" <?php echo $product['is_featured'] ? 'disabled' : ''; ?>>
                                        <?php echo $product['name']; ?> 
                                        <?php echo $product['is_featured'] ? '(Already a bestseller)' : ''; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" checked>
                                    <label class="form-check-label" for="is_featured">Mark as Bestseller</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" name="update_bestseller" value="1">
                                <button type="submit" class="btn btn-primary w-100">Add Bestseller</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Current Bestsellers -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h4>Current Bestsellers</h4>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <!-- Date Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="bestsellers.php" method="get" class="row g-3">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Total Units Sold</th>
                                    <th>Total Revenue</th>
                                    <th>Current Stock</th>
                                    <th>Bestseller Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($topSellingProducts) > 0): ?>
                                    <?php foreach($topSellingProducts as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="50" class="me-3">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $product['name']; ?></h6>
                                                    <small class="text-muted">SKU: <?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $product['category_name']; ?></td>
                                        <td><?php echo $product['total_quantity'] ?? 0; ?></td>
                                        <td><?php echo formatPrice($product['total_sales'] ?? 0); ?></td>
                                        <td>
                                            <?php if($product['quantity'] > 20): ?>
                                            <span class="badge bg-success">Good (<?php echo $product['quantity']; ?>)</span>
                                            <?php elseif($product['quantity'] > 0): ?>
                                            <span class="badge bg-warning">Low (<?php echo $product['quantity']; ?>)</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="bestsellers.php" method="post" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="update_bestseller" value="1">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input" id="featured_<?php echo $product['id']; ?>" 
                                                           name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?> 
                                                           onchange="this.form.submit()">
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">Edit</a>
                                            <a href="../product.php?slug=<?php echo $product['slug']; ?>" class="action-btn view-btn" target="_blank">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No sales data found for the selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Export Options -->
                    <div class="card-body border-top">
                        <button class="btn btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button class="btn btn-success ms-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script>
        function exportToExcel() {
            // Add Excel export functionality here
            alert('Excel export functionality will be implemented here');
        }
    </script>
</body>
</html>
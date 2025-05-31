<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get inventory data
$lowStock = getLowStockProducts();
$outOfStock = getOutOfStockProducts();
$inventoryValue = getInventoryValue();
$inventoryByCategory = getInventoryByCategory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <h4>Inventory Report</h4>
                        <div>
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                            <button class="btn btn-success ms-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Overview -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stats-card bg-primary text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Total Products</h5>
                                    <h3><?php echo getTotalProducts(); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-danger text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Low Stock Items</h5>
                                    <h3><?php echo count($lowStock); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-warning text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Out of Stock</h5>
                                    <h3><?php echo count($outOfStock); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-success text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Inventory Value</h5>
                                    <h3><?php echo formatPrice($inventoryValue); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Charts -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Inventory by Category</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Stock Level Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="stockLevelChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Low Stock Alert</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($lowStock as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="40" class="me-3">
                                                    <?php echo $product['name']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo $product['category_name']; ?></td>
                                            <td><?php echo $product['quantity']; ?></td>
                                            <td>20</td>
                                            <td>
                                                <?php if($product['quantity'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                                <?php else: ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Update Stock</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Value by Category -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Inventory Value by Category</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Number of Products</th>
                                            <th>Total Stock</th>
                                            <th>Total Value</th>
                                            <th>Average Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($inventoryByCategory as $category): ?>
                                        <tr>
                                            <td><?php echo $category['name']; ?></td>
                                            <td><?php echo $category['product_count']; ?></td>
                                            <td><?php echo $category['total_stock']; ?></td>
                                            <td><?php echo formatPrice($category['total_value']); ?></td>
                                            <td><?php echo formatPrice($category['average_price']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
    <script>
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($inventoryByCategory, 'name')); ?>,
                datasets: [{
                    label: 'Number of Products',
                    data: <?php echo json_encode(array_column($inventoryByCategory, 'product_count')); ?>,
                    backgroundColor: '#B22222'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Stock Level Chart
        const stockLevelCtx = document.getElementById('stockLevelChart').getContext('2d');
        new Chart(stockLevelCtx, {
            type: 'pie',
            data: {
                labels: ['Out of Stock', 'Low Stock', 'Optimal Stock'],
                datasets: [{
                    data: [
                        <?php echo count($outOfStock); ?>,
                        <?php echo count($lowStock) - count($outOfStock); ?>,
                        <?php echo getTotalProducts() - count($lowStock); ?>
                    ],
                    backgroundColor: [
                        '#dc3545',
                        '#ffc107',
                        '#28a745'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function exportToExcel() {
            // Add Excel export functionality
            alert('Excel export functionality will be implemented');
        }
    </script>
</body>
</html>
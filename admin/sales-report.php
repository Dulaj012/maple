<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get sales data
$salesData = getSalesData($startDate, $endDate);
$totalSales = array_sum(array_column($salesData, 'total'));
$totalOrders = count($salesData);
$averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

// Get top selling products
$topProducts = getTopSellingProducts(5);

// Get sales by category
$salesByCategory = getSalesByCategory($startDate, $endDate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Admin Dashboard</title>
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
                        <h4>Sales Report</h4>
                        <div>
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                            <button class="btn btn-success ms-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="sales-report.php" method="get" class="row g-3">
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
                                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sales Overview -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stats-card bg-primary text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Total Sales</h5>
                                    <h3><?php echo formatPrice($totalSales); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-success text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Total Orders</h5>
                                    <h3><?php echo $totalOrders; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-info text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Average Order Value</h5>
                                    <h3><?php echo formatPrice($averageOrderValue); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card bg-warning text-white">
                                <div class="stats-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="stats-info">
                                    <h5>Conversion Rate</h5>
                                    <h3>3.2%</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Chart -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Sales Trend</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Sales by Category</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Selling Products -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Top Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Units Sold</th>
                                            <th>Revenue</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($topProducts as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="40" class="me-3">
                                                    <?php echo $product['name']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo $product['total_quantity']; ?></td>
                                            <td><?php echo formatPrice($product['total_sales']); ?></td>
                                            <td>
                                                <div class="sparkline">
                                                    <i class="fas fa-arrow-up text-success"></i>
                                                    <span class="text-success">12%</span>
                                                </div>
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
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($salesData, 'date')); ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?php echo json_encode(array_column($salesData, 'total')); ?>,
                    borderColor: '#B22222',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(178, 34, 34, 0.1)'
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
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($salesByCategory, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($salesByCategory, 'total')); ?>,
                    backgroundColor: [
                        '#B22222',
                        '#FFD700',
                        '#800000',
                        '#CD5C5C',
                        '#8B0000'
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
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get dashboard stats
$totalProducts = getTotalProducts();
$totalCustomers = getTotalCustomers();
$totalOrders = getTotalOrders();
$totalSales = getTotalSales();

// Get recent orders
$recentOrders = getRecentOrders(5);

// Get top selling products
$topSellingProducts = getTopSellingProducts(5);

// Get sales data for chart
$currentMonth = date('Y-m');
$startDate = date('Y-m-01');
$endDate = date('Y-m-t');
$salesData = getSalesData($startDate, $endDate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MapleCart</title>
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
                <div class="admin-card-header">
                    <h2>Dashboard</h2>
                    <p>Welcome back, <?php echo $_SESSION['user_name']; ?>!</p>
                </div>

                <!-- Stats Cards -->
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h5>Total Products</h5>
                        <h3><?php echo $totalProducts; ?></h3>
                        <p class="up"><i class="fas fa-arrow-up"></i> 10% from previous month</p>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Total Customers</h5>
                        <h3><?php echo $totalCustomers; ?></h3>
                        <p class="up"><i class="fas fa-arrow-up"></i> 15% from previous month</p>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h5>Total Orders</h5>
                        <h3><?php echo $totalOrders; ?></h3>
                        <p class="up"><i class="fas fa-arrow-up"></i> 8% from previous month</p>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h5>Total Sales</h5>
                        <h3><?php echo formatPrice($totalSales); ?></h3>
                        <p class="up"><i class="fas fa-arrow-up"></i> 12% from previous month</p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mt-4">
                    <div class="col-lg-8">
                        <div class="admin-chart">
                            <h4>Sales Overview (<?php echo date('F Y'); ?>)</h4>
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="admin-chart">
                            <h4>Top Selling Products</h4>
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Best Selling Products -->
                <div class="admin-card mt-4">
                    <div class="admin-card-header">
                        <h4>Best Selling Products</h4>
                        <a href="bestsellers.php" class="btn btn-primary btn-sm">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total Units Sold</th>
                                    <th>Total Revenue</th>
                                    <th>Average Rating</th>
                                    <th>Stock Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($topSellingProducts as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="50" class="me-3">
                                            <div>
                                                <h6 class="mb-0"><?php echo $product['name']; ?></h6>
                                                <small class="text-muted"><?php echo $product['category_name']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $product['total_quantity']; ?></td>
                                    <td><?php echo formatPrice($product['total_sales']); ?></td>
                                    <td>
                                        <div class="rating">
                                            <i class="fas fa-star text-warning"></i>
                                            <span>4.5</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($product['quantity'] > 0): ?>
                                        <span class="badge bg-success">In Stock (<?php echo $product['quantity']; ?>)</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">Edit</a>
                                        <a href="../product.php?slug=<?php echo $product['slug']; ?>" class="action-btn view-btn" target="_blank">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="admin-card mt-4">
                    <div class="admin-card-header">
                        <h4>Recent Orders</h4>
                        <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_number']; ?></td>
                                    <td><?php echo $order['shipping_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="order-status <?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view-order.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">View</a>
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    
    <!-- Chart Initialization -->
    <script>
        // Sales Chart
        const salesChartCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesChartCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                    $labels = [];
                    $data = [];
                    for ($i = 1; $i <= date('t'); $i++) {
                        $date = date('Y-m-') . sprintf('%02d', $i);
                        $labels[] = "'" . $i . "'";
                        
                        $found = false;
                        foreach ($salesData as $sale) {
                            if (date('Y-m-d', strtotime($sale['date'])) === $date) {
                                $data[] = $sale['total'];
                                $found = true;
                                break;
                            }
                        }
                        
                        if (!$found) {
                            $data[] = 0;
                        }
                    }
                    echo implode(', ', $labels);
                    ?>
                ],
                datasets: [{
                    label: 'Sales ($)',
                    data: [<?php echo implode(', ', $data); ?>],
                    backgroundColor: 'rgba(178, 34, 34, 0.2)',
                    borderColor: 'rgba(178, 34, 34, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Products Chart
        const productsChartCtx = document.getElementById('productsChart').getContext('2d');
        const productsChart = new Chart(productsChartCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php
                    $productLabels = [];
                    $productData = [];
                    foreach ($topSellingProducts as $product) {
                        $productLabels[] = "'" . $product['name'] . "'";
                        $productData[] = $product['total_quantity'];
                    }
                    echo implode(', ', $productLabels);
                    ?>
                ],
                datasets: [{
                    label: 'Units Sold',
                    data: [<?php echo implode(', ', $productData); ?>],
                    backgroundColor: [
                        'rgba(178, 34, 34, 0.8)',
                        'rgba(255, 215, 0, 0.8)',
                        'rgba(128, 0, 0, 0.8)',
                        'rgba(205, 92, 92, 0.8)',
                        'rgba(139, 0, 0, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get all customers with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$customers = getCustomers($limit, $offset);
$totalCustomers = getTotalCustomers();
$totalPages = ceil($totalCustomers / $limit);

// Handle customer status toggle
if (isset($_POST['toggle_status'])) {
    $userId = (int)$_POST['user_id'];
    $status = clean($_POST['status']);
    
    $sql = "UPDATE users SET status = '$status' WHERE id = $userId AND role = 'customer'";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = 'Customer status updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to update customer status.';
    }
    header('Location: customers.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin Dashboard</title>
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
                        <h4>Customers</h4>
                        <div>
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print List
                            </button>
                            <button class="btn btn-success ms-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers as $customer): 
                                    $purchaseHistory = getCustomerPurchaseHistory($customer['id']);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/placeholder.png" alt="<?php echo $customer['name']; ?>" 
                                                 width="40" height="40" class="rounded-circle me-2">
                                            <?php echo $customer['name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $customer['email']; ?></td>
                                    <td><?php echo $customer['phone'] ?: 'N/A'; ?></td>
                                    <td>
                                        <?php if ($customer['city'] && $customer['country']): ?>
                                            <?php echo $customer['city'] . ', ' . $customer['country']; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="orders.php?customer=<?php echo $customer['id']; ?>">
                                            <?php echo $purchaseHistory['order_count']; ?> orders
                                        </a>
                                    </td>
                                    <td><?php echo formatPrice($purchaseHistory['total_spent']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <form action="customers.php" method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                            <input type="hidden" name="toggle_status" value="1">
                                            <input type="hidden" name="status" 
                                                   value="<?php echo $customer['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $customer['status'] === 'active' ? 'btn-success' : 'btn-danger'; ?>">
                                                <?php echo ucfirst($customer['status']); ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="view-customer.php?id=<?php echo $customer['id']; ?>">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="orders.php?customer=<?php echo $customer['id']; ?>">
                                                        <i class="fas fa-shopping-bag"></i> View Orders
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="mailto:<?php echo $customer['email']; ?>">
                                                        <i class="fas fa-envelope"></i> Send Email
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="admin-pagination">
                        <?php echo generatePagination($page, $totalPages, 'customers.php?'); ?>
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
    <script>
        function exportToExcel() {
            // Add Excel export functionality here
            alert('Excel export functionality will be implemented here');
        }
    </script>
</body>
</html>
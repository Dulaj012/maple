<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = 'dashboard.php';
    header('Location: login.php');
    exit;
}

// Get user data
$user = getUserById($_SESSION['user_id']);

// Get user orders
$orders = getUserOrders($_SESSION['user_id']);

// Get user wishlist
$wishlist = getWishlistItems($_SESSION['user_id']);

// Active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - MapleCart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Dashboard Section -->
    <section class="dashboard-section">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-user">
                            <div class="avatar">
                                <img src="https://via.placeholder.com/100x100" alt="User Avatar">
                            </div>
                            <h4><?php echo $user['name']; ?></h4>
                            <p><?php echo $user['email']; ?></p>
                        </div>
                        <div class="dashboard-menu">
                            <ul>
                                <li>
                                    <a href="dashboard.php?tab=dashboard" class="<?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="dashboard.php?tab=orders" class="<?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                                        <i class="fas fa-shopping-bag"></i> Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="dashboard.php?tab=wishlist" class="<?php echo $activeTab === 'wishlist' ? 'active' : ''; ?>">
                                        <i class="fas fa-heart"></i> Wishlist
                                    </a>
                                </li>
                                <li>
                                    <a href="dashboard.php?tab=profile" class="<?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a href="dashboard.php?tab=change-password" class="<?php echo $activeTab === 'change-password' ? 'active' : ''; ?>">
                                        <i class="fas fa-lock"></i> Change Password
                                    </a>
                                </li>
                                <li class="logout">
                                    <a href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="col-lg-9">
                    <div class="dashboard-content">
                        <?php if ($activeTab === 'dashboard'): ?>
                        <!-- Dashboard Overview -->
                        <div class="content-title">
                            <h3>Dashboard</h3>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <h4>Total Orders</h4>
                                    <p><?php echo count($orders); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h4>Wishlist Items</h4>
                                    <p><?php echo count($wishlist); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-card">
                                    <div class="dashboard-card-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h4>Cart Items</h4>
                                    <p><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h4>Recent Orders</h4>
                            
                            <?php if (count($orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="order-table">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recentOrders = array_slice($orders, 0, 5);
                                        foreach($recentOrders as $order): 
                                        ?>
                                        <tr>
                                            <td class="order-id">#<?php echo $order['order_number']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <span class="order-status <?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">You have no orders yet.</div>
                            <?php endif; ?>
                            
                            <?php if (count($orders) > 5): ?>
                            <div class="text-end mt-3">
                                <a href="dashboard.php?tab=orders" class="btn btn-outline-primary btn-sm">View All Orders</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php elseif ($activeTab === 'orders'): ?>
                        <!-- Orders -->
                        <div class="content-title">
                            <h3>My Orders</h3>
                        </div>
                        
                        <?php if (count($orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $order['order_number']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <span class="order-status <?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatPrice($order['total_amount']); ?></td>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">You have no orders yet.</div>
                        <?php endif; ?>
                        
                        <?php elseif ($activeTab === 'wishlist'): ?>
                        <!-- Wishlist -->
                        <div class="content-title">
                            <h3>My Wishlist</h3>
                        </div>
                        
                        <?php if (count($wishlist) > 0): ?>
                        <div class="table-responsive">
                            <table class="wishlist-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Stock Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($wishlist as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="cart-product">
                                                <div class="cart-product-img">
                                                    <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                                </div>
                                                <div class="cart-product-info">
                                                    <h4><a href="product.php?slug=<?php echo $item['slug']; ?>"><?php echo $item['name']; ?></a></h4>
                                                    <a href="process/remove-from-wishlist.php?id=<?php echo $item['product_id']; ?>" class="remove-product">Remove</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cart-product-price">
                                            <?php if($item['sale_price']): ?>
                                            <span class="current-price"><?php echo formatPrice($item['sale_price']); ?></span>
                                            <span class="old-price"><?php echo formatPrice($item['price']); ?></span>
                                            <?php else: ?>
                                            <span class="current-price"><?php echo formatPrice($item['price']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($item['quantity'] > 0): ?>
                                            <span class="text-success">In Stock</span>
                                            <?php else: ?>
                                            <span class="text-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="process/add-to-cart.php" method="post">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">Your wishlist is empty.</div>
                        <?php endif; ?>
                        
                        <?php elseif ($activeTab === 'profile'): ?>
                        <!-- Profile -->
                        <div class="content-title">
                            <h3>My Profile</h3>
                        </div>
                        
                        <form action="process/update-profile.php" method="post" class="profile-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" value="<?php echo $user['address']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $user['city']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="<?php echo $user['state']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="zipcode">Zipcode</label>
                                        <input type="text" class="form-control" id="zipcode" name="zipcode" value="<?php echo $user['zipcode']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="<?php echo $user['country']; ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-save">Save Changes</button>
                        </form>
                        
                        <?php elseif ($activeTab === 'change-password'): ?>
                        <!-- Change Password -->
                        <div class="content-title">
                            <h3>Change Password</h3>
                        </div>
                        
                        <form action="process/change-password.php" method="post" class="profile-form">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-save">Update Password</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
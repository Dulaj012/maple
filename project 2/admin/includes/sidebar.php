<div class="admin-sidebar">
    <div class="admin-toggle">
        <i class="fas fa-bars"></i>
    </div>
    <div class="admin-menu">
        <div class="admin-menu-category">Main</div>
        <a href="index.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span class="menu-title">Dashboard</span>
        </a>
        
        <div class="admin-menu-category">Catalog</div>
        <a href="products.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span class="menu-title">Products</span>
        </a>
        <a href="categories.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i>
            <span class="menu-title">Categories</span>
        </a>
        
        <div class="admin-menu-category">Sales</div>
        <a href="orders.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i>
            <span class="menu-title">Orders</span>
        </a>
        <a href="customers.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span class="menu-title">Customers</span>
        </a>
        <a href="coupons.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active' : ''; ?>">
            <i class="fas fa-tag"></i>
            <span class="menu-title">Coupons</span>
        </a>
        
        <div class="admin-menu-category">Reports</div>
        <a href="sales-report.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'sales-report.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span class="menu-title">Sales Report</span>
        </a>
        <a href="inventory-report.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'inventory-report.php' ? 'active' : ''; ?>">
            <i class="fas fa-cubes"></i>
            <span class="menu-title">Inventory Report</span>
        </a>
        
        <div class="admin-menu-category">Settings</div>
        <a href="profile.php" class="admin-menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span class="menu-title">Profile</span>
        </a>
        <a href="../logout.php" class="admin-menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span class="menu-title">Logout</span>
        </a>
    </div>
</div>
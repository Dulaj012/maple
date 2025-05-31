<header class="header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">Maple<span>Cart</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="shop.php?category=food-beverages">Food & Beverages</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=beauty-personal-care">Beauty & Personal Care</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=apparel-accessories">Apparel & Accessories</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=home-living">Home & Living</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=health-wellness">Health & Wellness</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=baby-kids">Baby & Kids</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=specialty-seasonal">Specialty & Seasonal</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=gourmet-luxury">Gourmet & Luxury</a></li>
                            <li><a class="dropdown-item" href="shop.php?category=pet-supplies">Pet Supplies</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="icons">
                    <a href="#" class="search-toggle" id="searchToggle"><i class="fas fa-search"></i></a>
                    <a href="wishlist.php"><i class="fas fa-heart"></i><span class="badge">
                        <?php echo isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : '0'; ?>
                    </span></a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i><span class="badge">
                        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : '0'; ?>
                    </span></a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-user"></i></a>
                    <?php else: ?>
                    <a href="login.php"><i class="fas fa-user"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<!-- Search Panel -->
<div class="search-panel" id="searchPanel">
  <div class="container">
    <!-- Move the close button here -->
    <button class="search-close" id="searchClose" aria-label="Close search">
      <i class="fas fa-times"></i>
    </button>
    <div class="search-panel-content">
      <form action="shop.php" method="get" class="search-form">
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search for products..." id="searchInput" autocomplete="off">
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

</header>
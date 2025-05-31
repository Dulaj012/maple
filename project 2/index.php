<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MapleCart</title>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <span class="subtitle">100% Canadian Imported Goods in Sri Lanka</span>
                        <h1>Premium Canadian Products</h1>
                        <p>Delivered to Your Doorstep.</p>
                        <div class="hero-buttons">
                            <a href="shop.php" class="btn btn-primary">Shop Now</a>
                            <a href="about.php" class="btn btn-outline-primary ms-2">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/1111.jpg" alt="Fresh fruits and vegetables" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Fast Shipping</h4>
                        <p>On all orders</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h4>Secure Payment</h4>
                        <p>100% secure payment</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h4>30 Day Return</h4>
                        <p>30 day money back</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p>Call or Text us anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Categories -->
    <section class="categories-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Premium Products</h2>
                <p>Discover the range of premium Canadian products</p>
            </div>
            <div class="category-tabs">
                <ul class="nav nav-pills justify-content-center mb-4" id="categoryTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All Products</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vegetables-tab" data-bs-toggle="pill" data-bs-target="#vegetables" type="button" role="tab" aria-controls="vegetables" aria-selected="false">Vegetables</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fruits-tab" data-bs-toggle="pill" data-bs-target="#fruits" type="button" role="tab" aria-controls="fruits" aria-selected="false">Fruits</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nuts-tab" data-bs-toggle="pill" data-bs-target="#nuts" type="button" role="tab" aria-controls="nuts" aria-selected="false">Nuts</button>
                    </li>
                </ul>
                <div class="tab-content" id="categoryTabContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <div class="row">
                            <?php
                            $products = getFeaturedProducts(8);
                            foreach($products as $product) {
                                include 'includes/product-card.php';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vegetables" role="tabpanel" aria-labelledby="vegetables-tab">
                        <div class="row">
                            <?php
                            $products = getProductsByCategory(1, 8);
                            foreach($products as $product) {
                                include 'includes/product-card.php';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="fruits" role="tabpanel" aria-labelledby="fruits-tab">
                        <div class="row">
                            <?php
                            $products = getProductsByCategory(2, 8);
                            foreach($products as $product) {
                                include 'includes/product-card.php';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nuts" role="tabpanel" aria-labelledby="nuts-tab">
                        <div class="row">
                            <?php
                            $products = getProductsByCategory(3, 8);
                            foreach($products as $product) {
                                include 'includes/product-card.php';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="shop.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Banner Section -->
    <section class="banner-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="banner-box banner-box-1">
                        <div class="banner-content">
                            <h3>Your Favourite Chocolates</h3>
                            <p>in Our Store</p>
                            <a href="shop.php?category=fruits" class="btn btn-light">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="banner-box banner-box-2">
                        <div class="banner-content">
                            <h3>Fresh Organic Vegetables</h3>
                            <p>100% Organic</p>
                            <a href="shop.php?category=vegetables" class="btn btn-light">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bestseller Products -->
    <section class="bestseller-section">
        <div class="container">
            <div class="section-title">
                <h2>Bestseller Products</h2>
                <p>Our most popular products based on sales</p>
            </div>
            <div class="bestseller-container">
                <div class="bestseller-slider">
                    <?php
                    // Double the products array for seamless scrolling
                    $products = getBestsellerProducts(4);
                    $allProducts = array_merge($products, $products);
                    foreach($allProducts as $product) {
                        include 'includes/product-card.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Logos Section -->
    <section class="brands-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Trusted Brands</h2>
                <p>Premium brands we proudly carry</p>
            </div>
            <div class="brand-slider-container">
                <div class="brand-slider">
                    <!-- Double the logos for seamless infinite scroll -->
                    <div class="brand-logo">
                        <img src="assets/images/Featured brands/10.png" alt="Brand 1">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/9.png" alt="Brand 2">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/8.png" alt="Brand 3">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/7.png" alt="Brand 4">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/6.png" alt="Brand 5">
                    </div>
                    <!-- Duplicate logos for seamless scrolling -->
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/5.png" alt="Brand 6">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/4.png" alt="Brand 7">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/3.png" alt="Brand 8">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/2.png" alt="Brand 9">
                    </div>
                    <div class="brand-logo">
                    <img src="assets/images/Featured brands/1.png" alt="Brand 10">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-box">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="newsletter-content">
                            <h3>Subscribe to Our Newsletter</h3>
                            <p>Get the latest updates, offers and special announcements delivered directly to your inbox.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <form action="process/subscribe.php" method="post" class="newsletter-form">
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Your Email Address" required>
                                <button type="submit" class="btn btn-primary">Subscribe</button>
                            </div>
                        </form>
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
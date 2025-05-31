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
    <title>About Us - MapleCart</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-hero-content">
                        <h1>Bringing Canada's Finest to Sri Lanka</h1>
                        <p class="lead">Since 2025, we've been bridging continents by bringing premium Canadian products directly to Sri Lankan consumers.</p>
                        <div class="about-stats">
                            <div class="stat-item">
                                <h3>100+</h3>
                                <p>Happy Customers</p>
                            </div>
                            <div class="stat-item">
                                <h3>500+</h3>
                                <p>Products</p>
                            </div>
                            <div class="stat-item">
                                <h3>99%</h3>
                                <p>Satisfaction Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-hero-image">
                        <img src="assets/images/1122.jpg" alt="MapleCart Team" class="img-fluid rounded-3">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-story">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-story-image">
                        <img src="assets/images/100.jpg" alt="Our Story" class="img-fluid rounded-3">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-story-content">
                        <h2>Our Story</h2>
                        <p>MapleCart began with a simple idea: to bring the quality and authenticity of Canadian products to Sri Lankan households. Our founder, having lived in both countries, recognized the growing demand for premium Canadian goods in Sri Lanka.</p>
                        <p>What started as a small operation has grown into Sri Lanka's leading platform for authentic Canadian imports. We take pride in our rigorous quality control, direct relationships with Canadian suppliers, and commitment to customer satisfaction.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="about-values">
        <div class="container">
            <div class="section-title text-center">
                <h2>Our Values</h2>
                <p>The principles that guide everything we do</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4>Quality Assurance</h4>
                        <p>We never compromise on quality. Every product undergoes strict quality checks before reaching our customers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Customer First</h4>
                        <p>Our customers are at the heart of everything we do. Their satisfaction is our top priority.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Transparency</h4>
                        <p>We believe in honest business practices and maintaining clear communication with our customers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="about-testimonials">
        <div class="container">
            <div class="section-title text-center">
                <h2>What Our Customers Say</h2>
                <p>Real feedback from satisfied customers</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"The quality of Canadian products from MapleCart is exceptional. Their customer service is outstanding!"</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg" alt="Lisa Fernando">
                            <div>
                                <h5>Lisa Fernando</h5>
                                <p>Regular Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Fast delivery and authentic products. MapleCart has become my go-to store for Canadian goods."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg" alt="Raj Patel">
                            <div>
                                <h5>Raj Patel</h5>
                                <p>Verified Buyer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Their maple syrup is the real deal! Love the variety of Canadian products they offer."</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg" alt="Sarah Perera">
                            <div>
                                <h5>Sarah Perera</h5>
                                <p>Loyal Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container">
            <div class="cta-content text-center">
                <h2>Experience the Best of Canada in Sri Lanka</h2>
                <p>Join the customers who trust MapleCart for authentic Canadian products.</p>
                <div class="cta-buttons">
                    <a href="shop.php" class="btn btn-primary">Shop Now</a>
                    <a href="contact.php" class="btn btn-outline-primary ms-3">Contact Us</a>
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
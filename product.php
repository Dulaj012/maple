<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get product slug
if (!isset($_GET['slug'])) {
    header('Location: shop.php');
    exit;
}

$slug = clean($_GET['slug']);
$product = getProductBySlug($slug);

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Get related products from same category
$relatedProducts = getProductsByCategory($product['category_id'], 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - MapleCart</title>
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

    <!-- Product Detail Section -->
    <section class="product-detail-section">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6">
                    <div class="product-detail-images">
                        <?php if($product['sale_price']): ?>
                        <span class="product-detail-badge sale">Sale</span>
                        <?php endif; ?>
                        
                        <div class="product-detail-main-image">
                            <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" id="main-image">
                        </div>
                        
                        <?php if($product['gallery']): ?>
                        <div class="product-detail-thumbnails">
                            <div class="product-detail-thumbnail active" data-image="<?php echo $product['image']; ?>">
                                <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            </div>
                            <?php 
                            $gallery = explode(',', $product['gallery']);
                            foreach($gallery as $image):
                            ?>
                            <div class="product-detail-thumbnail" data-image="<?php echo trim($image); ?>">
                                <img src="assets/images/products/<?php echo trim($image); ?>" alt="<?php echo $product['name']; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-detail-info">
                        <div class="product-detail-category">
                            <a href="shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a>
                        </div>
                        
                        <h1 class="product-detail-title"><?php echo $product['name']; ?></h1>
                        
                        <div class="product-detail-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="reviews-count">(4.5 / 5)</span>
                        </div>
                        
                        <div class="product-detail-price">
                            <?php if($product['sale_price']): ?>
                            <span class="product-detail-current-price"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="product-detail-old-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                            <span class="product-detail-current-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-detail-stock">
                            <?php if($product['quantity'] > 0): ?>
                            <span class="in-stock">In Stock (<?php echo $product['quantity']; ?> available)</span>
                            <?php else: ?>
                            <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-detail-description">
                            <p><?php echo $product['description']; ?></p>
                        </div>
                        
                        <?php if($product['quantity'] > 0): ?>
                        <form action="process/add-to-cart.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="product-detail-quantity">
                                <div class="quantity-input">
                                    <button type="button" class="quantity-btn minus">-</button>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                                    <button type="button" class="quantity-btn plus">+</button>
                                </div>
                            </div>
                            
                            <div class="product-detail-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                                <?php if(isLoggedIn()): ?>
                                <a href="process/add-to-wishlist.php?id=<?php echo $product['id']; ?>" class="btn btn-wishlist"><i class="fas fa-heart"></i></a>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-wishlist" title="Login to add to wishlist"><i class="fas fa-heart"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php endif; ?>
                        
                        <div class="product-detail-meta">
                            <p><span>SKU:</span> <?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            <p><span>Category:</span> <a href="shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="product-detail-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specifications">Specifications</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Reviews</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="description">
                        <div class="product-detail-tab-content">
                            <h4>Product Description</h4>
                            <p><?php echo $product['description']; ?></p>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="specifications">
                        <div class="product-detail-tab-content">
                            <h4>Product Specifications</h4>
                            <table class="product-specs-table">
                                <tr>
                                    <th>Weight</th>
                                    <td>500g</td>
                                </tr>
                                <tr>
                                    <th>Origin</th>
                                    <td>Canada</td>
                                </tr>
                                <tr>
                                    <th>Quality</th>
                                    <td>Premium Grade</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="reviews">
                        <div class="product-detail-tab-content">
                            <h4>Customer Reviews</h4>
                            
                            <!-- Sample Reviews -->
                            <div class="product-review">
                                <div class="product-review-header">
                                    <div class="product-review-author">
                                        <img src="assets/images/customer-1.jpg" alt="Customer">
                                        <div>
                                            <h5>John Doe</h5>
                                            <div class="product-review-date">2 days ago</div>
                                        </div>
                                    </div>
                                    <div class="product-review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="product-review-content">
                                    <p>Excellent product! The quality is outstanding and delivery was prompt.</p>
                                </div>
                            </div>
                            
                            <!-- Review Form -->
                            <?php if(isLoggedIn()): ?>
                            <div class="review-form">
                                <h5>Write a Review</h5>
                                <form action="process/add-review.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Your Rating</label>
                                        <div class="star-rating">
                                            <input type="radio" name="rating" value="5" id="star5">
                                            <label for="star5"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="4" id="star4">
                                            <label for="star4"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="3" id="star3">
                                            <label for="star3"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="2" id="star2">
                                            <label for="star2"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="1" id="star1">
                                            <label for="star1"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="review">Your Review</label>
                                        <textarea class="form-control" id="review" name="review" rows="5" required></textarea>
                                    </div>
                
                                    
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <p class="text-center mt-4">
                                Please <a href="login.php">login</a> to write a review.
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (count($relatedProducts) > 0): ?>
            <div class="section-title mt-5">
                <h2>Related Products</h2>
                <p>You may also like these products</p>
            </div>
            
            <div class="row">
                <?php 
                foreach($relatedProducts as $relatedProduct) {
                    if ($relatedProduct['id'] != $product['id']) {
                        include 'includes/product-card.php';
                    }
                }
                ?>
            </div>
            <?php endif; ?>
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
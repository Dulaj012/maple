<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get cart items
$cartItems = getCartItems();
$cartTotal = getCartTotal();

// Process coupon code
$couponDiscount = 0;
$totalAfterDiscount = $cartTotal;
$couponMessage = '';
$couponValid = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coupon_code'])) {
    $couponCode = clean($_POST['coupon_code']);
    $couponResult = validateCoupon($couponCode, $cartTotal);
    
    if ($couponResult['valid']) {
        $couponValid = true;
        $couponDiscount = $couponResult['discount'];
        $totalAfterDiscount = $couponResult['total_after_discount'];
        $couponMessage = 'Coupon applied successfully!';
        $_SESSION['coupon'] = [
            'code' => $couponCode,
            'id' => $couponResult['coupon']['id'],
            'discount' => $couponDiscount
        ];
    } else {
        $couponMessage = $couponResult['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Fruitables</title>
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

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <div class="section-title">
                <h2>Shopping Cart</h2>
                <p>Review your items and proceed to checkout</p>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($cartItems)): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-product">
                                            <div class="cart-product-img">
                                                <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                            </div>
                                            <div class="cart-product-info">
                                                <h4><?php echo $item['name']; ?></h4>
                                                <a href="process/remove-from-cart.php?id=<?php echo $item['id']; ?>" class="remove-product">Remove</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart-product-price"><?php echo formatPrice($item['price']); ?></td>
                                    <td>
                                        <div class="cart-quantity">
                                            <form action="process/update-cart.php" method="post">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <button type="button" class="quantity-btn minus">-</button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                                <button type="button" class="quantity-btn plus">+</button>
                                                <button type="submit" class="btn btn-sm btn-primary ms-2 update-cart" disabled>Update</button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="cart-total"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    <td>
                                        <a href="process/remove-from-cart.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-action">
                        <a href="shop.php"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                        <a href="process/clear-cart.php">Clear Cart <i class="fas fa-trash"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3>Cart Summary</h3>
                        <div class="cart-summary-item">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        
                        <?php if ($couponValid): ?>
                        <div class="cart-summary-item">
                            <span>Discount</span>
                            <span>-<?php echo formatPrice($couponDiscount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cart-summary-item">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        
                        <div class="cart-summary-item total">
                            <span>Total</span>
                            <span><?php echo formatPrice($totalAfterDiscount); ?></span>
                        </div>
                        
                        <div class="cart-coupon">
                            <h5>Coupon Code</h5>
                            <form action="cart.php" method="post">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="coupon_code" placeholder="Enter coupon code">
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </form>
                            
                            <?php if (!empty($couponMessage)): ?>
                            <div class="alert <?php echo $couponValid ? 'alert-success' : 'alert-danger'; ?> mt-2">
                                <?php echo $couponMessage; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary cart-checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <p>Your cart is empty.</p>
                <a href="shop.php" class="btn btn-primary mt-3">Continue Shopping</a>
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
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = 'Your cart is empty. Please add products before checkout.';
    header('Location: cart.php');
    exit;
}

// Get cart items and total
$cartItems = getCartItems();
$cartTotal = getCartTotal();

// Initialize variables
$couponDiscount = 0;
$totalAfterDiscount = $cartTotal;

// Apply coupon discount if exists and valid
if (isset($_SESSION['coupon']) && !empty($_SESSION['coupon'])) {
    // Validate coupon again to ensure it's still valid
    $couponResult = validateCoupon($_SESSION['coupon']['code'], $cartTotal);
    if ($couponResult['valid']) {
        $couponDiscount = $couponResult['discount'];
        $totalAfterDiscount = $cartTotal - $couponDiscount;
    } else {
        // Coupon no longer valid, remove from session
        unset($_SESSION['coupon']);
    }
}

// Get user data if logged in
$user = null;
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingName = clean($_POST['shipping_name']);
    $shippingEmail = clean($_POST['shipping_email']);
    $shippingPhone = clean($_POST['shipping_phone']);
    $shippingAddress = clean($_POST['shipping_address']);
    $shippingCity = clean($_POST['shipping_city']);
    $shippingState = clean($_POST['shipping_state']);
    $shippingZipcode = clean($_POST['shipping_zipcode']);
    $shippingCountry = clean($_POST['shipping_country']);
    $paymentMethod = clean($_POST['payment_method']);
    $notes = isset($_POST['notes']) ? clean($_POST['notes']) : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($shippingName)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($shippingEmail)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($shippingEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($shippingPhone)) {
        $errors[] = 'Phone number is required.';
    }
    
    if (empty($shippingAddress)) {
        $errors[] = 'Address is required.';
    }
    
    if (empty($shippingCity)) {
        $errors[] = 'City is required.';
    }
    
    if (empty($shippingState)) {
        $errors[] = 'State is required.';
    }
    
    if (empty($shippingZipcode)) {
        $errors[] = 'Zipcode is required.';
    }
    
    if (empty($shippingCountry)) {
        $errors[] = 'Country is required.';
    }
    
    if (empty($paymentMethod)) {
        $errors[] = 'Payment method is required.';
    }
    
    if (empty($errors)) {
        // Prepare order data
        $orderData = [
            'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null,
            'total_amount' => $totalAfterDiscount,
            'shipping_name' => $shippingName,
            'shipping_email' => $shippingEmail,
            'shipping_phone' => $shippingPhone,
            'shipping_address' => $shippingAddress,
            'shipping_city' => $shippingCity,
            'shipping_state' => $shippingState,
            'shipping_zipcode' => $shippingZipcode,
            'shipping_country' => $shippingCountry,
            'payment_method' => $paymentMethod,
            'notes' => $notes
        ];
        
        // Create order
        $orderId = createOrder($orderData);
        
        if ($orderId) {
            // Apply coupon to order if exists
            if (isset($_SESSION['coupon'])) {
                applyCoupon($orderId, $_SESSION['coupon']['id'], $couponDiscount);
                unset($_SESSION['coupon']);
            }
            
            // Send order confirmation email
            sendOrderConfirmation($orderId);
            
            // Redirect to order confirmation page
            $_SESSION['order_id'] = $orderId;
            header('Location: order-confirmation.php');
            exit;
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fruitables</title>
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

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <div class="section-title">
                <h2>Checkout</h2>
                <p>Complete your order by providing your shipping details</p>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form action="checkout.php" method="post">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-form">
                            <h3>Shipping Details</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_name">Full Name</label>
                                        <input type="text" class="form-control" id="shipping_name" name="shipping_name" value="<?php echo $user ? $user['name'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_email">Email Address</label>
                                        <input type="email" class="form-control" id="shipping_email" name="shipping_email" value="<?php echo $user ? $user['email'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" value="<?php echo $user ? $user['phone'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_address">Address</label>
                                        <input type="text" class="form-control" id="shipping_address" name="shipping_address" value="<?php echo $user ? $user['address'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_city">City</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="<?php echo $user ? $user['city'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_state">State</label>
                                        <input type="text" class="form-control" id="shipping_state" name="shipping_state" value="<?php echo $user ? $user['state'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_zipcode">Zipcode</label>
                                        <input type="text" class="form-control" id="shipping_zipcode" name="shipping_zipcode" value="<?php echo $user ? $user['zipcode'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_country">Country</label>
                                        <input type="text" class="form-control" id="shipping_country" name="shipping_country" value="<?php echo $user ? $user['country'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes">Order Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="mt-4">Payment Method</h3>
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <div class="payment-method-header">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                                        <label class="form-check-label" for="bank_transfer">Direct Bank Transfer</label>
                                    </div>
                                    <div class="payment-method-content" id="bank_transfer-content">
                                        <p>Make your payment directly to our bank account. Please use your Order ID as the payment reference. Your order will be shipped after we receive the payment.</p>
                                        <div class="bank-details mt-3">
                                            <h5>Bank Account Details:</h5>
                                            <ul class="list-unstyled">
                                                <li><strong>Bank:</strong> Royal Bank of Canada (RBC)</li>
                                                <li><strong>Account Name:</strong> MapleCart Inc.</li>
                                                <li><strong>Account Number:</strong> 1234-5678-9012</li>
                                                <li><strong>Branch Code:</strong> 001</li>
                                                <li><strong>SWIFT Code:</strong> ROYCCAT2XXX</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h3>Order Summary</h3>
                            <div class="order-summary-products">
                                <?php foreach($cartItems as $item): ?>
                                <div class="order-product">
                                    <div class="order-product-name">
                                        <?php echo $item['name']; ?>
                                        <span>x<?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="order-product-total">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-summary-totals">
                                <div class="order-summary-item">
                                    <span>Subtotal</span>
                                    <span><?php echo formatPrice($cartTotal); ?></span>
                                </div>
                                
                                <?php if ($couponDiscount > 0): ?>
                                <div class="order-summary-item">
                                    <span>Discount</span>
                                    <span>-<?php echo formatPrice($couponDiscount); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="order-summary-item">
                                    <span>Shipping</span>
                                    <span>Free</span>
                                </div>
                                
                                <div class="order-summary-item total">
                                    <span>Total</span>
                                    <span><?php echo formatPrice($totalAfterDiscount); ?></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary place-order-btn">Place Order</button>
                        </div>
                    </div>
                </div>
            </form>
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
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if order ID is set
if (!isset($_SESSION['order_id'])) {
    header('Location: shop.php');
    exit;
}

$orderId = $_SESSION['order_id'];
$order = getOrderById($orderId);

// Verify order exists
if (!$order) {
    header('Location: shop.php');
    exit;
}

$orderItems = getOrderItems($orderId);

// Clear order ID from session after successful retrieval
unset($_SESSION['order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MapleCart</title>
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

    <!-- Order Confirmation Section -->
    <section class="verification-section">
        <div class="container">
            <div class="verification-box">
                <div class="verification-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Thank You for Your Order!</h2>
                <p>Your order has been received and we will process only after the payment is cleared.</p>
                
                <div class="order-details mt-4">
                    <div class="row">
                        <div class="col-md-6 text-start">
                            <h5>Order Details</h5>
                            <p><strong>Order Number:</strong> #<?php echo $order['order_number']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        </div>
                        <div class="col-md-6 text-start">
                            <h5>Shipping Address</h5>
                            <p><?php echo $order['shipping_name']; ?></p>
                            <p><?php echo $order['shipping_address']; ?></p>
                            <p><?php echo $order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_zipcode']; ?></p>
                            <p><?php echo $order['shipping_country']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items mt-4">
                    <h5 class="text-start">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['total']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="payment-instructions mt-4">
                    <h5>Payment Instructions</h5>
                    <p>Pay directly to our bank account and send us the receipt.</p>
                    <div class="bank-details">
                        <ul class="list-unstyled">
                            <li><strong>Bank Name:</strong> Royal Bank of Canada (RBC)</li>
                            <li><strong>Account Name:</strong> Canada2Lanka Imports</li>
                            <li><strong>Account Number:</strong> 123456789</li>
                            <li><strong>SWIFT Code:</strong> ROYCCAT2</li>
                            <li><strong>Branch:</strong> Toronto Main Branch</li>
                            <li><strong>Reference:</strong> Your Order ID</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p>An order confirmation email has been sent to <strong><?php echo $order['shipping_email']; ?></strong>.</p>
                    <p>Please send the payment receipt into our whatsapp number with your order number.</p>
                    
                    <div class="mt-4">
                        <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                        <?php if(isLoggedIn()): ?>
                        <a href="order-detail.php?id=<?php echo $orderId; ?>" class="btn btn-outline-primary ms-2">View Order Details</a>
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
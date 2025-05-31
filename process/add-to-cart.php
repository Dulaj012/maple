<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    try {
        if (addToCart($productId, $quantity)) {
            $response = [
                'success' => true,
                'message' => 'Product added to cart successfully!',
                'cart_count' => count($_SESSION['cart']),
                'cart_total' => formatPrice(getCartTotal())
            ];
        } else {
            throw new Exception('Failed to add product to cart.');
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit;
}

// Invalid request
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    try {
        $conn->begin_transaction();
        
        // Lock the row for update to prevent race conditions
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ? AND status = 'active' FOR UPDATE");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            if ($product['quantity'] >= $quantity) {
                if (addToCart($productId, $quantity)) {
                    $conn->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product added to cart successfully!',
                        'cart_count' => count($_SESSION['cart'])
                    ]);
                    exit;
                }
            } else {
                throw new Exception('Requested quantity not available in stock.');
            }
        } else {
            throw new Exception('Product not found or inactive.');
        }
        
        $conn->rollback();
        throw new Exception('Failed to add product to cart.');
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
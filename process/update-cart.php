<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    try {
        $conn->begin_transaction();
        
        // Lock the row for update
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ? AND status = 'active' FOR UPDATE");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            if ($quantity <= $product['quantity']) {
                if (updateCart($productId, $quantity)) {
                    $conn->commit();
                    
                    // Calculate new totals
                    $cartTotal = getCartTotal();
                    $itemCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cart updated successfully!',
                        'cart_total' => formatPrice($cartTotal),
                        'item_count' => $itemCount
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
        throw new Exception('Failed to update cart.');
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
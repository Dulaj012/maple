<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Get product details to check stock
    $sql = "SELECT quantity FROM products WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $product = $result->fetch_assoc()) {
        // Check if requested quantity is available
        if ($product['quantity'] >= $quantity) {
            if (addToCart($productId, $quantity)) {
                $_SESSION['success_message'] = 'Product added to cart successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to add product to cart.';
            }
        } else {
            $_SESSION['error_message'] = 'Requested quantity not available in stock.';
        }
    } else {
        $_SESSION['error_message'] = 'Product not found or inactive.';
    }
    
    // Redirect back
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../shop.php');
    }
    exit;
} else {
    header('Location: ../shop.php');
    exit;
}
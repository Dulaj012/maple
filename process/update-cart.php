<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Check stock availability
    $sql = "SELECT quantity FROM products WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $product = $result->fetch_assoc()) {
        if ($quantity <= $product['quantity']) {
            if (updateCart($productId, $quantity)) {
                $_SESSION['success_message'] = 'Cart updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to update cart.';
            }
        } else {
            $_SESSION['error_message'] = 'Requested quantity not available in stock.';
        }
    } else {
        $_SESSION['error_message'] = 'Product not found or inactive.';
    }
    
    header('Location: ../cart.php');
    exit;
} else {
    header('Location: ../cart.php');
    exit;
}
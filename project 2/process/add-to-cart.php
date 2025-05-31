<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    if (addToCart($productId, $quantity)) {
        // Set success message
        $_SESSION['success_message'] = 'Product added to cart successfully!';
    } else {
        // Set error message
        $_SESSION['error_message'] = 'Failed to add product to cart.';
    }
    
    // Redirect back to referring page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../index.php');
    }
    exit;
} else {
    // Invalid request
    header('Location: ../index.php');
    exit;
}
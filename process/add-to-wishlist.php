<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isLoggedIn()) {
    $productId = (int)$_GET['id'];
    $userId = $_SESSION['user_id'];
    
    if (addToWishlist($userId, $productId)) {
        // Set success message
        $_SESSION['success_message'] = 'Product added to wishlist successfully!';
    } else {
        // Set error message
        $_SESSION['error_message'] = 'Failed to add product to wishlist.';
    }
    
    // Redirect back to referring page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../index.php');
    }
    exit;
} else {
    // Invalid request or not logged in
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'Please login to add items to your wishlist.';
    }
    header('Location: ../login.php');
    exit;
}
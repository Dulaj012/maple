<?php
session_start();
require_once '../includes/functions.php';

// Clear the cart
clearCart();

// Set success message
$_SESSION['success_message'] = 'Cart has been cleared successfully.';

// Redirect back to cart page
header('Location: ../cart.php');
exit;
?>
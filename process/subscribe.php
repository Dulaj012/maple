<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = clean($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    if (subscribeNewsletter($email)) {
        $_SESSION['success_message'] = 'Thank you for subscribing to our newsletter!';
    } else {
        $_SESSION['error_message'] = 'Failed to subscribe. Please try again.';
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
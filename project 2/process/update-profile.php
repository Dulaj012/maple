<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => clean($_POST['name']),
        'phone' => clean($_POST['phone']),
        'address' => clean($_POST['address']),
        'city' => clean($_POST['city']),
        'state' => clean($_POST['state']),
        'zipcode' => clean($_POST['zipcode']),
        'country' => clean($_POST['country'])
    ];

    if (updateUserProfile($_SESSION['user_id'], $data)) {
        $_SESSION['user_name'] = $data['name']; // Update session name
        $_SESSION['success_message'] = 'Profile updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
    }
}

// Redirect back to profile page
header('Location: ../dashboard.php?tab=profile');
exit;
?>
<?php
session_start();
require_once 'includes/functions.php';

// Call logout function
logoutUser();

// Redirect to home page
header('Location: index.php');
exit;
?>
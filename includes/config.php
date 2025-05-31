<?php
// Database configuration
define('DB_HOST', 'localhost'); 
define('DB_USER', 'root');
define('DB_PASS', '200202');
define('DB_NAME', 'maplecart_db');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db(DB_NAME);

// Set charset
$conn->set_charset("utf8mb4");

// Create necessary tables
require_once 'database/tables.php';

// Initialize admin user
require_once 'database/init.php';
<?php
// Database configuration
define('DB_HOST', 'localhost'); 
define('DB_USER', 'root'); // Your MySQL username
define('DB_PASS', '200202'); // Your MySQL password - common for MAMP
define('DB_NAME', 'fruitables_db');

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Set character set
$conn->set_charset("utf8mb4");

// Create tables with proper collation
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(100),
        state VARCHAR(100),
        zipcode VARCHAR(20),
        country VARCHAR(100),
        role ENUM('admin', 'customer') DEFAULT 'customer',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "categories" => "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        parent_id INT(11) UNSIGNED DEFAULT NULL,
        description TEXT,
        image VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "products" => "CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT(11) UNSIGNED,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2),
        image VARCHAR(255),
        gallery TEXT,
        quantity INT(11) DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        total_amount DECIMAL(10,2) NOT NULL,
        discount DECIMAL(10,2) DEFAULT 0,
        shipping_name VARCHAR(100) NOT NULL,
        shipping_email VARCHAR(100) NOT NULL,
        shipping_phone VARCHAR(20) NOT NULL,
        shipping_address TEXT NOT NULL,
        shipping_city VARCHAR(100) NOT NULL,
        shipping_state VARCHAR(100) NOT NULL,
        shipping_zipcode VARCHAR(20) NOT NULL,
        shipping_country VARCHAR(100) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT(11) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "wishlist" => "CREATE TABLE IF NOT EXISTS wishlist (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    "coupons" => "CREATE TABLE IF NOT EXISTS coupons (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        value DECIMAL(10,2) NOT NULL,
        min_spend DECIMAL(10,2) DEFAULT NULL,
        max_discount DECIMAL(10,2) DEFAULT NULL,
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        usage_limit INT DEFAULT NULL,
        usage_count INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

// Create tables
foreach ($tables as $table => $sql) {
    if ($conn->query($sql) === FALSE) {
        die("Error creating $table table: " . $conn->error);
    }
}

// Insert admin user if not exists
$checkAdmin = $conn->query("SELECT id FROM users WHERE email = 'admin@maplecart.com'");
if ($checkAdmin->num_rows == 0) {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@maplecart.com', '$adminPassword', 'admin')";
    $conn->query($sql);
}

// Insert sample categories if not exists
$checkCategories = $conn->query("SELECT id FROM categories LIMIT 1");
if ($checkCategories->num_rows == 0) {
    $categories = [
        ['Food & Beverages', 'food-beverages', NULL],
        ['Maple Syrup & Products', 'maple-syrup-products', 1],
        ['Snack Foods', 'snack-foods', 1],
        ['Health Foods', 'health-foods', 1],
        ['Beverages', 'beverages', 1],
        ['Packaged Foods', 'packaged-foods', 1],
        ['Beauty & Personal Care', 'beauty-personal-care', NULL],
        ['Skincare Products', 'skincare-products', 7],
        ['Hair Care', 'hair-care', 7],
        ['Bath & Body', 'bath-body', 7],
        ['Cosmetics', 'cosmetics', 7]
    ];

    foreach ($categories as $category) {
        $name = $category[0];
        $slug = $category[1];
        $parentId = $category[2];
        $sql = "INSERT INTO categories (name, slug, parent_id) VALUES ('$name', '$slug', " . ($parentId ? $parentId : "NULL") . ")";
        $conn->query($sql);
    }
}

// Insert sample products if not exists
$checkProducts = $conn->query("SELECT id FROM products LIMIT 1");
if ($checkProducts->num_rows == 0) {
    $products = [
        [2, 'Pure Maple Syrup', 'pure-maple-syrup', 'Premium Canadian maple syrup', 19.99, 100, 1],
        [2, 'Maple Cookies', 'maple-cookies', 'Delicious maple-flavored cookies', 9.99, 200, 1],
        [8, 'Maple Body Lotion', 'maple-body-lotion', 'Nourishing body lotion with maple extract', 24.99, 50, 1]
    ];

    foreach ($products as $product) {
        $sql = "INSERT INTO products (category_id, name, slug, description, price, quantity, is_featured) 
                VALUES ($product[0], '$product[1]', '$product[2]', '$product[3]', $product[4], $product[5], $product[6])";
        $conn->query($sql);
    }
}
?>
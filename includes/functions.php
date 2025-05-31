<?php
// User Authentication Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Login user
function loginUser($email, $password) {
    global $conn;
    $email = clean($email);
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND status = 'active'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    return false;
}

// Register user
function registerUser($name, $email, $password) {
    global $conn;
    $name = clean($name);
    $email = clean($email);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashedPassword')";
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    return false;
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    return true;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Check if email exists
function emailExists($email) {
    global $conn;
    $email = clean($email);
    
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Add product function
function addProduct($data) {
    global $conn;
    
    $categoryId = (int)$data['category_id'];
    $name = clean($data['name']);
    $slug = generateSlug($name);
    $description = clean($data['description']);
    $price = (float)$data['price'];
    $salePrice = !empty($data['sale_price']) ? (float)$data['sale_price'] : "NULL";
    $image = clean($data['image']);
    $gallery = !empty($data['gallery']) ? "'" . clean($data['gallery']) . "'" : "NULL";
    $quantity = (int)$data['quantity'];
    $isFeatured = isset($data['is_featured']) ? 1 : 0;
    $status = isset($data['status']) ? clean($data['status']) : 'active';
    
    $sql = "INSERT INTO products (
        category_id, 
        name, 
        slug, 
        description, 
        price, 
        sale_price, 
        image, 
        gallery, 
        quantity, 
        is_featured,
        status
    ) VALUES (
        $categoryId,
        '$name',
        '$slug',
        '$description',
        $price,
        $salePrice,
        '$image',
        $gallery,
        $quantity,
        $isFeatured,
        '$status'
    )";
    
    return $conn->query($sql);
}

// Update product function
function updateProduct($id, $data) {
    global $conn;
    
    $categoryId = (int)$data['category_id'];
    $name = clean($data['name']);
    $slug = generateSlug($name);
    $description = clean($data['description']);
    $price = (float)$data['price'];
    $salePrice = !empty($data['sale_price']) ? (float)$data['sale_price'] : "NULL";
    $image = clean($data['image']);
    $gallery = !empty($data['gallery']) ? "'" . clean($data['gallery']) . "'" : "NULL";
    $quantity = (int)$data['quantity'];
    $isFeatured = isset($data['is_featured']) ? 1 : 0;
    $status = clean($data['status']);
    
    $sql = "UPDATE products SET 
            category_id = $categoryId, 
            name = '$name', 
            slug = '$slug', 
            description = '$description', 
            price = $price, 
            sale_price = $salePrice, 
            image = '$image', 
            gallery = $gallery, 
            quantity = $quantity, 
            is_featured = $isFeatured, 
            status = '$status' 
            WHERE id = $id";
    
    return $conn->query($sql);
}

// Delete product function
function deleteProduct($id) {
    global $conn;
    $id = (int)$id;
    
    // Delete product image first
    $product = getProductById($id);
    if ($product && $product['image']) {
        $imagePath = __DIR__ . '/../assets/images/products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $sql = "DELETE FROM products WHERE id = $id";
    return $conn->query($sql);
}

// Get product by ID
function getProductById($id) {
    global $conn;
    $id = (int)$id;
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = $id";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get product by slug
function getProductBySlug($slug) {
    global $conn;
    $slug = clean($slug);
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.slug = '$slug'";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get all products
function getProducts($limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $offset = $offset ?: 0;
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get products by category
function getProductsByCategory($categoryId, $limit = null, $offset = null) {
    global $conn;
    $categoryId = (int)$categoryId;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = $categoryId 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $offset = $offset ?: 0;
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get featured products
function getFeaturedProducts($limit = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_featured = 1 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get bestseller products
function getBestsellerProducts($limit = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, 
            COUNT(oi.id) as total_orders, 
            SUM(oi.quantity) as total_quantity 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN order_items oi ON p.id = oi.product_id 
            GROUP BY p.id 
            ORDER BY total_quantity DESC";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Search products
function searchProducts($keyword, $limit = null, $offset = null) {
    global $conn;
    $keyword = clean($keyword);
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE '%$keyword%' 
               OR p.description LIKE '%$keyword%' 
               OR c.name LIKE '%$keyword%' 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $offset = $offset ?: 0;
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get total products
function getTotalProducts() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM products";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return (int)$row['total'];
}

// Get total products by category
function getTotalProductsByCategory($categoryId) {
    global $conn;
    $categoryId = (int)$categoryId;
    
    $sql = "SELECT COUNT(*) as total FROM products WHERE category_id = $categoryId";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return (int)$row['total'];
}

// Get total products by search
function getTotalProductsBySearch($keyword) {
    global $conn;
    $keyword = clean($keyword);
    
    $sql = "SELECT COUNT(*) as total 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE '%$keyword%' 
               OR p.description LIKE '%$keyword%' 
               OR c.name LIKE '%$keyword%'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return (int)$row['total'];
}

// Upload file function
function uploadFile($file, $destination) {
    // Check if file was uploaded without errors
    if ($file['error'] === 0) {
        $fileName = $file['name'];
        $fileTemp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check if extension is allowed
        if (in_array($fileExt, $allowed)) {
            // Check file size (5MB max)
            if ($fileSize <= 5242880) {
                // Generate unique filename
                $fileNameNew = uniqid('', true) . '.' . $fileExt;
                $fileDestination = $destination . $fileNameNew;
                
                // Upload file
                if (move_uploaded_file($fileTemp, $fileDestination)) {
                    return $fileNameNew;
                }
            }
        }
    }
    
    return false;
}

// Clean input function
function clean($string) {
    global $conn;
    return $conn->real_escape_string(trim($string));
}

// Generate slug function
function generateSlug($string) {
    // Replace non letter or digits by -
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    
    // Transliterate
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    
    // Remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);
    
    // Trim
    $string = trim($string, '-');
    
    // Remove duplicate -
    $string = preg_replace('~-+~', '-', $string);
    
    // Lowercase
    $string = strtolower($string);
    
    return $string;
}

// Format price function
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Get total customers count
function getTotalCustomers() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

// Get total orders count
function getTotalOrders() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM orders";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int)$row['total'];
}

// Get total sales amount
function getTotalSales() {
    global $conn;
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (float)($row['total'] ?? 0);
}

// Get recent orders
function getRecentOrders($limit = 5) {
    global $conn;
    $sql = "SELECT o.*, u.name as user_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT $limit";
    $result = $conn->query($sql);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

// Get top selling products
function getTopSellingProducts($limit = 5) {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name,
            COUNT(oi.id) as total_orders,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.total) as total_sales
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id
            ORDER BY total_quantity DESC
            LIMIT $limit";
    
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Get sales data for chart
function getSalesData($startDate, $endDate) {
    global $conn;
    $sql = "SELECT DATE(created_at) as date, 
            SUM(total_amount) as total
            FROM orders 
            WHERE created_at BETWEEN '$startDate' AND '$endDate'
            AND status != 'cancelled'
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get admin orders with pagination
function getAdminOrders($limit = 10, $offset = 0) {
    global $conn;
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT $offset, $limit";
    
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}
?>
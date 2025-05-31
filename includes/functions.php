<?php
// Database connection
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Clean input data
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get user by ID
function getUserById($userId) {
    global $conn;
    $userId = (int)$userId;
    $sql = "SELECT * FROM users WHERE id = $userId";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get user by email
function getUserByEmail($email) {
    global $conn;
    $email = $conn->real_escape_string($email);
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Check if email exists
function emailExists($email) {
    return getUserByEmail($email) !== null;
}

// Register new user
function registerUser($name, $email, $password) {
    global $conn;
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password, role, status, created_at) 
            VALUES ('$name', '$email', '$hashedPassword', 'customer', 'active', NOW())";
    
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    return false;
}

// Login user
function loginUser($email, $password) {
    $user = getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

// Logout user
function logoutUser() {
    session_destroy();
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Get categories
function getCategories() {
    global $conn;
    $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
    $result = $conn->query($sql);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

// Get category by ID
function getCategoryById($categoryId) {
    global $conn;
    $categoryId = (int)$categoryId;
    $sql = "SELECT * FROM categories WHERE id = $categoryId";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get category by slug
function getCategoryBySlug($slug) {
    global $conn;
    $slug = $conn->real_escape_string($slug);
    $sql = "SELECT * FROM categories WHERE slug = '$slug'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get products
function getProducts($limit = null, $offset = 0) {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Get product by ID
function getProductById($productId) {
    global $conn;
    $productId = (int)$productId;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = $productId";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get product by slug
function getProductBySlug($slug) {
    global $conn;
    $slug = $conn->real_escape_string($slug);
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.slug = '$slug'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get products by category
function getProductsByCategory($categoryId, $limit = null, $offset = 0) {
    global $conn;
    $categoryId = (int)$categoryId;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = $categoryId AND p.status = 'active' 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
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
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_featured = 1 AND p.status = 'active' 
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
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
            COUNT(oi.product_id) as total_quantity,
            SUM(oi.total) as total_sales
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE p.status = 'active' AND (o.status != 'cancelled' OR o.status IS NULL)
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
function searchProducts($search, $limit = null, $offset = 0) {
    global $conn;
    $search = $conn->real_escape_string($search);
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' 
            AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%') 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
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
    $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Get total products by category
function getTotalProductsByCategory($categoryId) {
    global $conn;
    $categoryId = (int)$categoryId;
    $sql = "SELECT COUNT(*) as total FROM products WHERE category_id = $categoryId AND status = 'active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Get total products by search
function getTotalProductsBySearch($search) {
    global $conn;
    $search = $conn->real_escape_string($search);
    $sql = "SELECT COUNT(*) as total 
            FROM products 
            WHERE status = 'active' 
            AND (name LIKE '%$search%' OR description LIKE '%$search%')";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Add to cart
function addToCart($productId, $quantity = 1) {
    $product = getProductById($productId);
    
    if (!$product) {
        return false;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity,
            'price' => $product['sale_price'] ? $product['sale_price'] : $product['price']
        ];
    }
    
    return true;
}

// Get cart items
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $productId => $item) {
        $product = getProductById($productId);
        if ($product) {
            $product['cart_quantity'] = $item['quantity'];
            $items[] = $product;
        }
    }
    
    return $items;
}

// Get cart total
function getCartTotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $productId => $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Remove cart item
function removeCartItem($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    return false;
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Update cart quantity
function updateCartQuantity($productId, $quantity) {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        return true;
    }
    return false;
}

// Add to wishlist
function addToWishlist($userId, $productId) {
    global $conn;
    $userId = (int)$userId;
    $productId = (int)$productId;
    
    // Check if already in wishlist
    $sql = "SELECT * FROM wishlist WHERE user_id = $userId AND product_id = $productId";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        $sql = "INSERT INTO wishlist (user_id, product_id, created_at) VALUES ($userId, $productId, NOW())";
        return $conn->query($sql);
    }
    return true;
}

// Get wishlist items
function getWishlistItems($userId) {
    global $conn;
    $userId = (int)$userId;
    $sql = "SELECT p.*, w.created_at as added_at 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = $userId 
            ORDER BY w.created_at DESC";
    
    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

// Remove from wishlist
function removeFromWishlist($userId, $productId) {
    global $conn;
    $userId = (int)$userId;
    $productId = (int)$productId;
    
    $sql = "DELETE FROM wishlist WHERE user_id = $userId AND product_id = $productId";
    return $conn->query($sql);
}

// Create order
function createOrder($data) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate order number
        $orderNumber = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        // Insert order
        $sql = "INSERT INTO orders (
                    order_number, user_id, total_amount,
                    shipping_name, shipping_email, shipping_phone,
                    shipping_address, shipping_city, shipping_state,
                    shipping_zipcode, shipping_country,
                    payment_method, payment_status,
                    notes, status, created_at
                ) VALUES (
                    '$orderNumber',
                    " . ($data['user_id'] ? $data['user_id'] : 'NULL') . ",
                    " . $data['total_amount'] . ",
                    '" . $conn->real_escape_string($data['shipping_name']) . "',
                    '" . $conn->real_escape_string($data['shipping_email']) . "',
                    '" . $conn->real_escape_string($data['shipping_phone']) . "',
                    '" . $conn->real_escape_string($data['shipping_address']) . "',
                    '" . $conn->real_escape_string($data['shipping_city']) . "',
                    '" . $conn->real_escape_string($data['shipping_state']) . "',
                    '" . $conn->real_escape_string($data['shipping_zipcode']) . "',
                    '" . $conn->real_escape_string($data['shipping_country']) . "',
                    '" . $conn->real_escape_string($data['payment_method']) . "',
                    'pending',
                    " . ($data['notes'] ? "'" . $conn->real_escape_string($data['notes']) . "'" : 'NULL') . ",
                    'pending',
                    NOW()
                )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating order: " . $conn->error);
        }
        
        $orderId = $conn->insert_id;
        
        // Insert order items
        foreach ($_SESSION['cart'] as $productId => $item) {
            $product = getProductById($productId);
            
            if (!$product) {
                throw new Exception("Product not found: " . $productId);
            }
            
            $sql = "INSERT INTO order_items (
                        order_id, product_id, price, quantity, total
                    ) VALUES (
                        $orderId,
                        $productId,
                        " . $item['price'] . ",
                        " . $item['quantity'] . ",
                        " . ($item['price'] * $item['quantity']) . "
                    )";
            
            if (!$conn->query($sql)) {
                throw new Exception("Error creating order item: " . $conn->error);
            }
            
            // Update product quantity
            $newQuantity = $product['quantity'] - $item['quantity'];
            $sql = "UPDATE products SET quantity = $newQuantity WHERE id = $productId";
            
            if (!$conn->query($sql)) {
                throw new Exception("Error updating product quantity: " . $conn->error);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        clearCart();
        
        return $orderId;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log($e->getMessage());
        return false;
    }
}

// Get order by ID
function getOrderById($orderId) {
    global $conn;
    $orderId = (int)$orderId;
    $sql = "SELECT * FROM orders WHERE id = $orderId";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Get order items
function getOrderItems($orderId) {
    global $conn;
    $orderId = (int)$orderId;
    $sql = "SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = $orderId";
    
    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

// Get user orders
function getUserOrders($userId) {
    global $conn;
    $userId = (int)$userId;
    $sql = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

// Update order status
function updateOrderStatus($orderId, $status) {
    global $conn;
    $orderId = (int)$orderId;
    $status = $conn->real_escape_string($status);
    
    $sql = "UPDATE orders SET status = '$status' WHERE id = $orderId";
    return $conn->query($sql);
}

// Get order by number
function getOrderByNumber($orderNumber) {
    global $conn;
    $orderNumber = $conn->real_escape_string($orderNumber);
    $sql = "SELECT * FROM orders WHERE order_number = '$orderNumber'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Send order confirmation email
function sendOrderConfirmation($orderId) {
    $order = getOrderById($orderId);
    $items = getOrderItems($orderId);
    
    if (!$order) {
        return false;
    }
    
    $to = $order['shipping_email'];
    $subject = "Order Confirmation - #" . $order['order_number'];
    
    $message = "<h2>Thank you for your order!</h2>";
    $message .= "<p>Your order #" . $order['order_number'] . " has been received and will be processed once payment is confirmed.</p>";
    
    $message .= "<h3>Order Details:</h3>";
    $message .= "<table>";
    foreach ($items as $item) {
        $message .= "<tr>";
        $message .= "<td>" . $item['name'] . "</td>";
        $message .= "<td>" . $item['quantity'] . "</td>";
        $message .= "<td>" . formatPrice($item['total']) . "</td>";
        $message .= "</tr>";
    }
    $message .= "</table>";
    
    $message .= "<p><strong>Total:</strong> " . formatPrice($order['total_amount']) . "</p>";
    
    $message .= "<h3>Shipping Address:</h3>";
    $message .= "<p>" . $order['shipping_name'] . "<br>";
    $message .= $order['shipping_address'] . "<br>";
    $message .= $order['shipping_city'] . ", " . $order['shipping_state'] . " " . $order['shipping_zipcode'] . "<br>";
    $message .= $order['shipping_country'] . "</p>";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: MapleCart <noreply@maplecart.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Subscribe to newsletter
function subscribeNewsletter($email) {
    global $conn;
    $email = $conn->real_escape_string($email);
    
    // Check if already subscribed
    $sql = "SELECT * FROM newsletter WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        $sql = "INSERT INTO newsletter (email, created_at) VALUES ('$email', NOW())";
        return $conn->query($sql);
    }
    return true;
}

// Generate pagination
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = '<ul class="pagination">';
    
    // Previous page
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage - 1) . '">&laquo;</a>';
        $pagination .= '</li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $pagination .= '<li class="page-item ' . ($i === $currentPage ? 'active' : '') . '">';
        $pagination .= '<a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
        $pagination .= '</li>';
    }
    
    // Next page
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage + 1) . '">&raquo;</a>';
        $pagination .= '</li>';
    }
    
    $pagination .= '</ul>';
    return $pagination;
}

// Update user profile
function updateUserProfile($userId, $data) {
    global $conn;
    $userId = (int)$userId;
    
    $sql = "UPDATE users SET 
            name = '" . $conn->real_escape_string($data['name']) . "',
            phone = " . ($data['phone'] ? "'" . $conn->real_escape_string($data['phone']) . "'" : 'NULL') . ",
            address = " . ($data['address'] ? "'" . $conn->real_escape_string($data['address']) . "'" : 'NULL') . ",
            city = " . ($data['city'] ? "'" . $conn->real_escape_string($data['city']) . "'" : 'NULL') . ",
            state = " . ($data['state'] ? "'" . $conn->real_escape_string($data['state']) . "'" : 'NULL') . ",
            zipcode = " . ($data['zipcode'] ? "'" . $conn->real_escape_string($data['zipcode']) . "'" : 'NULL') . ",
            country = " . ($data['country'] ? "'" . $conn->real_escape_string($data['country']) . "'" : 'NULL') . ",
            updated_at = NOW()
            WHERE id = $userId";
    
    return $conn->query($sql);
}

// Change user password
function changeUserPassword($userId, $currentPassword, $newPassword) {
    global $conn;
    $userId = (int)$userId;
    
    // Get current user
    $user = getUserById($userId);
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return false;
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = '$hashedPassword' WHERE id = $userId";
    
    return $conn->query($sql);
}

// Get admin orders
function getAdminOrders($limit = null, $offset = 0) {
    global $conn;
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

// Get total orders
function getTotalOrders() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM orders";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Get total customers
function getTotalCustomers() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Get total sales
function getTotalSales() {
    global $conn;
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get recent orders
function getRecentOrders($limit) {
    global $conn;
    $sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT $limit";
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

// Get sales data
function getSalesData($startDate, $endDate) {
    global $conn;
    $sql = "SELECT DATE(created_at) as date, SUM(total_amount) as total 
            FROM orders 
            WHERE status != 'cancelled' 
            AND created_at BETWEEN '$startDate' AND '$endDate 23:59:59' 
            GROUP BY DATE(created_at) 
            ORDER BY date";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get sales by category
function getSalesByCategory($startDate, $endDate) {
    global $conn;
    $sql = "SELECT c.name as category, SUM(oi.total) as total 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            JOIN categories c ON p.category_id = c.id 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.status != 'cancelled' 
            AND o.created_at BETWEEN '$startDate' AND '$endDate 23:59:59' 
            GROUP BY c.id 
            ORDER BY total DESC";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get customers
function getCustomers($limit = null, $offset = 0) {
    global $conn;
    $sql = "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT $offset, $limit";
    }
    
    $result = $conn->query($sql);
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    return $customers;
}

// Get customer purchase history
function getCustomerPurchaseHistory($userId) {
    global $conn;
    $userId = (int)$userId;
    $sql = "SELECT COUNT(*) as order_count, SUM(total_amount) as total_spent 
            FROM orders 
            WHERE user_id = $userId AND status != 'cancelled'";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Add category
function addCategory($data) {
    global $conn;
    $sql = "INSERT INTO categories (
                name, description, image, status, created_at
            ) VALUES (
                '" . $conn->real_escape_string($data['name']) . "',
                " . ($data['description'] ? "'" . $conn->real_escape_string($data['description']) . "'" : 'NULL') . ",
                " . ($data['image'] ? "'" . $conn->real_escape_string($data['image']) . "'" : 'NULL') . ",
                'active',
                NOW()
            )";
    
    return $conn->query($sql);
}

// Update category
function updateCategory($categoryId, $data) {
    global $conn;
    $categoryId = (int)$categoryId;
    
    $sql = "UPDATE categories SET 
            name = '" . $conn->real_escape_string($data['name']) . "',
            description = " . ($data['description'] ? "'" . $conn->real_escape_string($data['description']) . "'" : 'NULL') . ",
            image = " . ($data['image'] ? "'" . $conn->real_escape_string($data['image']) . "'" : 'NULL') . ",
            status = '" . $conn->real_escape_string($data['status']) . "',
            updated_at = NOW()
            WHERE id = $categoryId";
    
    return $conn->query($sql);
}

// Delete category
function deleteCategory($categoryId) {
    global $conn;
    $categoryId = (int)$categoryId;
    
    // Update products to uncategorized
    $sql = "UPDATE products SET category_id = NULL WHERE category_id = $categoryId";
    $conn->query($sql);
    
    // Delete category
    $sql = "DELETE FROM categories WHERE id = $categoryId";
    return $conn->query($sql);
}

// Add product
function addProduct($data) {
    global $conn;
    $sql = "INSERT INTO products (
                category_id, name, description, price, sale_price,
                image, quantity, is_featured, status,
                created_at
            ) VALUES (
                " . (int)$data['category_id'] . ",
                '" . $conn->real_escape_string($data['name']) . "',
                '" . $conn->real_escape_string($data['description']) . "',
                " . (float)$data['price'] . ",
                " . ($data['sale_price'] ? (float)$data['sale_price'] : 'NULL') . ",
                '" . $conn->real_escape_string($data['image']) . "',
                " . (int)$data['quantity'] . ",
                " . (int)$data['is_featured'] . ",
                'active',
                NOW()
            )";
    
    return $conn->query($sql);
}

// Update product
function updateProduct($productId, $data) {
    global $conn;
    $productId = (int)$productId;
    
    $sql = "UPDATE products SET 
            category_id = " . (int)$data['category_id'] . ",
            name = '" . $conn->real_escape_string($data['name']) . "',
            description = '" . $conn->real_escape_string($data['description']) . "',
            price = " . (float)$data['price'] . ",
            sale_price = " . ($data['sale_price'] ? (float)$data['sale_price'] : 'NULL') . ",
            image = '" . $conn->real_escape_string($data['image']) . "',
            quantity = " . (int)$data['quantity'] . ",
            is_featured = " . (int)$data['is_featured'] . ",
            status = '" . $conn->real_escape_string($data['status']) . "',
            updated_at = NOW()
            WHERE id = $productId";
    
    return $conn->query($sql);
}

// Delete product
function deleteProduct($productId) {
    global $conn;
    $productId = (int)$productId;
    
    // Delete from wishlist
    $sql = "DELETE FROM wishlist WHERE product_id = $productId";
    $conn->query($sql);
    
    // Delete product
    $sql = "DELETE FROM products WHERE id = $productId";
    return $conn->query($sql);
}

// Get low stock products
function getLowStockProducts() {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.quantity <= 20 AND p.status = 'active'";
    
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Get out of stock products
function getOutOfStockProducts() {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.quantity = 0 AND p.status = 'active'";
    
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Get inventory value
function getInventoryValue() {
    global $conn;
    $sql = "SELECT SUM(quantity * price) as total FROM products WHERE status = 'active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get inventory by category
function getInventoryByCategory() {
    global $conn;
    $sql = "SELECT c.name, 
            COUNT(p.id) as product_count,
            SUM(p.quantity) as total_stock,
            SUM(p.quantity * p.price) as total_value,
            AVG(p.price) as average_price
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            WHERE p.status = 'active'
            GROUP BY c.id";
    
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Upload file
function uploadFile($file, $destination) {
    // Check if file was uploaded without errors
    if ($file['error'] === 0) {
        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $destination . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName;
        }
    }
    return false;
}

// Send email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: MapleCart <noreply@maplecart.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Validate coupon
function validateCoupon($code, $cartTotal) {
    global $conn;
    $code = $conn->real_escape_string($code);
    
    $sql = "SELECT * FROM coupons WHERE code = '$code' AND status = 'active'";
    $result = $conn->query($sql);
    $coupon = $result->fetch_assoc();
    
    if (!$coupon) {
        return [
            'valid' => false,
            'message' => 'Invalid coupon code.'
        ];
    }
    
    // Check if coupon has expired
    if ($coupon['end_date'] && strtotime($coupon['end_date']) < time()) {
        return [
            'valid' => false,
            'message' => 'Coupon has expired.'
        ];
    }
    
    // Check if coupon has started
    if ($coupon['start_date'] && strtotime($coupon['start_date']) > time()) {
        return [
            'valid' => false,
            'message' => 'Coupon is not yet active.'
        ];
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
        return [
            'valid' => false,
            'message' => 'Coupon usage limit has been reached.'
        ];
    }
    
    // Check minimum spend
    if ($coupon['min_spend'] && $cartTotal < $coupon['min_spend']) {
        return [
            'valid' => false,
            'message' => 'Minimum spend of ' . formatPrice($coupon['min_spend']) . ' required.'
        ];
    }
    
    // Calculate discount
    $discount = 0;
    if ($coupon['type'] === 'percentage') {
        $discount = $cartTotal * ($coupon['value'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }
    } else {
        $discount = $coupon['value'];
    }
    
    return [
        'valid' => true,
        'discount' => $discount,
        'total_after_discount' => $cartTotal - $discount,
        'coupon' => $coupon
    ];
}

// Apply coupon to order
function applyCoupon($orderId, $couponId, $discount) {
    global $conn;
    $orderId = (int)$orderId;
    $couponId = (int)$couponId;
    $discount = (float)$discount;
    
    // Update order discount
    $sql = "UPDATE orders SET discount = $discount WHERE id = $orderId";
    $conn->query($sql);
    
    // Update coupon usage count
    $sql = "UPDATE coupons SET usage_count = usage_count + 1 WHERE id = $couponId";
    return $conn->query($sql);
}

// Get coupons
function getCoupons() {
    global $conn;
    $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $coupons = [];
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
    return $coupons;
}

// Get coupon by ID
function getCouponById($couponId) {
    global $conn;
    $couponId = (int)$couponId;
    $sql = "SELECT * FROM coupons WHERE id = $couponId";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Add coupon
function addCoupon($data) {
    global $conn;
    $sql = "INSERT INTO coupons (
                code, type, value, min_spend, max_discount,
                start_date, end_date, usage_limit,
                status, created_at
            ) VALUES (
                '" . $conn->real_escape_string($data['code']) . "',
                '" . $conn->real_escape_string($data['type']) . "',
                " . (float)$data['value'] . ",
                " . ($data['min_spend'] ? (float)$data['min_spend'] : 'NULL') . ",
                " . ($data['max_discount'] ? (float)$data['max_discount'] : 'NULL') . ",
                " . ($data['start_date'] ? "'" . $data['start_date'] . "'" : 'NULL') . ",
                " . ($data['end_date'] ? "'" . $data['end_date'] . "'" : 'NULL') . ",
                " . ($data['usage_limit'] ? (int)$data['usage_limit'] : 'NULL') . ",
                'active',
                NOW()
            )";
    
    return $conn->query($sql);
}

// Update coupon
function updateCoupon($couponId, $data) {
    global $conn;
    $couponId = (int)$couponId;
    
    $sql = "UPDATE coupons SET 
            code = '" . $conn->real_escape_string($data['code']) . "',
            type = '" . $conn->real_escape_string($data['type']) . "',
            value = " . (float)$data['value'] . ",
            min_spend = " . ($data['min_spend'] ? (float)$data['min_spend'] : 'NULL') . ",
            max_discount = " . ($data['max_discount'] ? (float)$data['max_discount'] : 'NULL') . ",
            start_date = " . ($data['start_date'] ? "'" . $data['start_date'] . "'" : 'NULL') . ",
            end_date = " . ($data['end_date'] ? "'" . $data['end_date'] . "'" : 'NULL') . ",
            usage_limit = " . ($data['usage_limit'] ? (int)$data['usage_limit'] : 'NULL') . ",
            status = '" . $conn->real_escape_string($data['status']) . "',
            updated_at = NOW()
            WHERE id = $couponId";
    
    return $conn->query($sql);
}

// Delete coupon
function deleteCoupon($couponId) {
    global $conn;
    $couponId = (int)$couponId;
    
    $sql = "DELETE FROM coupons WHERE id = $couponId";
    return $conn->query($sql);
}
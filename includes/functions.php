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
    
    $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
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
    
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Check if email exists
function emailExists($email) {
    global $conn;
    
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Cart Functions
function addToCart($productId, $quantity = 1) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'price' => $product['sale_price'] ?? $product['price'],
                'quantity' => $quantity,
                'image' => $product['image']
            ];
        }
        return true;
    }
    return false;
}

function updateCart($productId, $quantity) {
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
        return true;
    }
    return false;
}

function removeCartItem($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    return false;
}

function clearCart() {
    unset($_SESSION['cart']);
    return true;
}

function getCartItems() {
    if (!isset($_SESSION['cart'])) {
        return [];
    }
    return $_SESSION['cart'];
}

function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

// Product Functions
function getProducts($limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?, ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($limit) {
        $stmt->bind_param("ii", $offset, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

function getProductById($id) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.status = 'active'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProductBySlug($slug) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.slug = ? AND p.status = 'active'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Category Functions
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

function getCategoryById($id) {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getCategoryBySlug($slug) {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE slug = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Order Functions
function createOrder($data) {
    global $conn;
    
    $orderNumber = generateOrderNumber();
    
    $sql = "INSERT INTO orders (
        user_id, order_number, total_amount, 
        shipping_name, shipping_email, shipping_phone,
        shipping_address, shipping_city, shipping_state,
        shipping_zipcode, shipping_country,
        payment_method, notes
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isdsssssssss",
        $data['user_id'],
        $orderNumber,
        $data['total_amount'],
        $data['shipping_name'],
        $data['shipping_email'],
        $data['shipping_phone'],
        $data['shipping_address'],
        $data['shipping_city'],
        $data['shipping_state'],
        $data['shipping_zipcode'],
        $data['shipping_country'],
        $data['payment_method'],
        $data['notes']
    );
    
    if ($stmt->execute()) {
        $orderId = $stmt->insert_id;
        
        // Add order items
        foreach ($_SESSION['cart'] as $productId => $item) {
            $sql = "INSERT INTO order_items (
                order_id, product_id, name, price, quantity, total
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $total = $item['price'] * $item['quantity'];
            $stmt->bind_param(
                "iisdid",
                $orderId,
                $productId,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $total
            );
            $stmt->execute();
            
            // Update product quantity
            $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $productId);
            $stmt->execute();
        }
        
        // Clear cart
        clearCart();
        
        return $orderId;
    }
    
    return false;
}

function getOrderById($id) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getOrderItems($orderId) {
    global $conn;
    
    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    return $items;
}

// Helper Functions
function clean($string) {
    global $conn;
    return $conn->real_escape_string(trim($string));
}

function generateSlug($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);
    return $string;
}

function generateOrderNumber() {
    return 'MC' . date('Ymd') . rand(1000, 9999);
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function generatePagination($currentPage, $totalPages, $baseUrl) {
    $html = '<ul class="pagination">';
    
    // Previous page
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage - 1) . '">Previous</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next page
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul>';
    return $html;
}

// Send email function
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: MapleCart <noreply@maplecart.com>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Send order confirmation email
function sendOrderConfirmation($orderId) {
    $order = getOrderById($orderId);
    $items = getOrderItems($orderId);
    
    $subject = "Order Confirmation - #" . $order['order_number'];
    
    $message = "<h2>Thank you for your order!</h2>";
    $message .= "<p>Your order #" . $order['order_number'] . " has been received and will be processed once payment is confirmed.</p>";
    
    $message .= "<h3>Order Details:</h3>";
    $message .= "<table style='width: 100%; border-collapse: collapse;'>";
    $message .= "<tr><th style='text-align: left;'>Product</th><th>Quantity</th><th>Price</th></tr>";
    
    foreach ($items as $item) {
        $message .= "<tr>";
        $message .= "<td>" . $item['name'] . "</td>";
        $message .= "<td>" . $item['quantity'] . "</td>";
        $message .= "<td>" . formatPrice($item['total']) . "</td>";
        $message .= "</tr>";
    }
    
    $message .= "</table>";
    
    $message .= "<p><strong>Total:</strong> " . formatPrice($order['total_amount']) . "</p>";
    
    $message .= "<h3>Shipping Details:</h3>";
    $message .= "<p>" . $order['shipping_name'] . "<br>";
    $message .= $order['shipping_address'] . "<br>";
    $message .= $order['shipping_city'] . ", " . $order['shipping_state'] . " " . $order['shipping_zipcode'] . "<br>";
    $message .= $order['shipping_country'] . "</p>";
    
    return sendEmail($order['shipping_email'], $subject, $message);
}
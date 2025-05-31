<?php
// User Authentication Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Login user with rate limiting and security
function loginUser($email, $password) {
    global $conn;
    
    // Rate limiting
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
        if (time() - $_SESSION['last_attempt'] < 300) { // 5 minutes lockout
            return false;
        }
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt']);
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Update last login timestamp
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            return true;
        }
    }
    
    // Track failed login attempts
    $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
    $_SESSION['last_attempt'] = time();
    
    return false;
}

// Register user with validation
function registerUser($name, $email, $password) {
    global $conn;
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return false;
    }
    
    // Hash password with strong algorithm
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

// Cart Functions with Stock Management

// Add to cart with stock validation
function addToCart($productId, $quantity = 1) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, price, sale_price, quantity, image FROM products WHERE id = ? AND status = 'active' AND quantity >= ?");
    $stmt->bind_param("ii", $productId, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $product = $result->fetch_assoc()) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $currentQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['quantity'] : 0;
        $newQty = $currentQty + $quantity;
        
        // Check if total quantity exceeds stock
        if ($newQty > $product['quantity']) {
            return false;
        }
        
        $_SESSION['cart'][$productId] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ?? $product['price'],
            'image' => $product['image'],
            'quantity' => $newQty
        ];
        
        return true;
    }
    return false;
}

// Update cart with stock validation
function updateCart($productId, $quantity) {
    global $conn;
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $product = $result->fetch_assoc()) {
        if ($quantity <= $product['quantity']) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
            return true;
        }
    }
    return false;
}

// Remove from cart
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    return false;
}

// Get cart items with latest prices
function getCartItems() {
    global $conn;
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $productId => $item) {
        // Get latest product info
        $stmt = $conn->prepare("SELECT price, sale_price, quantity FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            // Update price if changed
            $currentPrice = $product['sale_price'] ?? $product['price'];
            $item['price'] = $currentPrice;
            
            // Adjust quantity if stock reduced
            if ($item['quantity'] > $product['quantity']) {
                $item['quantity'] = $product['quantity'];
            }
            
            $items[$productId] = $item;
        }
    }
    
    return $items;
}

// Calculate cart total with discounts
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Apply any active discounts
    if (isset($_SESSION['coupon'])) {
        $discount = calculateDiscount($total, $_SESSION['coupon']);
        $total -= $discount;
    }
    
    return $total;
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Order Functions

// Create order
function createOrder($data) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate unique order number
        $orderNumber = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        // Insert order
        $sql = "INSERT INTO orders (
            user_id, order_number, total_amount, shipping_name, 
            shipping_email, shipping_phone, shipping_address, 
            shipping_city, shipping_state, shipping_zipcode, 
            shipping_country, payment_method, notes
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
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }
        
        $orderId = $conn->insert_id;
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $sql = "INSERT INTO order_items (
                order_id, product_id, name, price, quantity, total
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $total = $item['price'] * $item['quantity'];
            $stmt->bind_param(
                "iisdid",
                $orderId,
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $total
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order items");
            }
            
            // Update product quantity
            $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update product quantity");
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
        return false;
    }
}

// Get order by ID
function getOrderById($orderId) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Get order items
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

// Update order status
function updateOrderStatus($orderId, $status) {
    global $conn;
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $orderId);
    
    return $stmt->execute();
}

// Utility Functions

// Clean input
function clean($string) {
    global $conn;
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Generate slug
function generateSlug($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    return strtolower($string);
}

// Send email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: MapleCart <noreply@maplecart.com>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
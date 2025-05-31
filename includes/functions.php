<?php
// User Authentication Functions

// Check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Verify session
    if (!isset($_SESSION['login_time']) || (time() - $_SESSION['login_time']) > 3600) {
        session_destroy();
        return false;
    }
    
    return true;
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Login user
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
    
    $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Reset login attempts
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Update last login
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            return true;
        }
    }
    
    // Track failed attempts
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['last_attempt'] = time();
    
    return false;
}

// Register user
function registerUser($name, $email, $password) {
    global $conn;
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return false;
    }
    
    // Hash password with Argon2id
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

// Cart Functions

// Add to cart
function addToCart($productId, $quantity = 1) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get product with row lock
        $sql = "SELECT * FROM products WHERE id = ? AND status = 'active' FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            // Check stock
            if ($product['quantity'] < $quantity) {
                throw new Exception("Insufficient stock");
            }
            
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Update cart
            if (isset($_SESSION['cart'][$productId])) {
                $newQuantity = $_SESSION['cart'][$productId]['quantity'] + $quantity;
                if ($newQuantity > $product['quantity']) {
                    throw new Exception("Insufficient stock");
                }
                $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['sale_price'] ?? $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity,
                    'stock' => $product['quantity']
                ];
            }
            
            $conn->commit();
            return true;
        }
        
        throw new Exception("Product not found");
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Update cart
function updateCart($productId, $quantity) {
    global $conn;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get product with row lock
        $sql = "SELECT quantity FROM products WHERE id = ? AND status = 'active' FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            if ($quantity > $product['quantity']) {
                throw new Exception("Insufficient stock");
            }
            
            if ($quantity > 0) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
            
            $conn->commit();
            return true;
        }
        
        throw new Exception("Product not found");
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Get cart items with real-time price updates
function getCartItems() {
    global $conn;
    
    if (!isset($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $productId => $item) {
        // Get current price
        $sql = "SELECT price, sale_price, quantity FROM products WHERE id = ? AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $product = $result->fetch_assoc()) {
            $item['price'] = $product['sale_price'] ?? $product['price'];
            $item['stock'] = $product['quantity'];
            
            // Adjust quantity if stock is insufficient
            if ($item['quantity'] > $product['quantity']) {
                $item['quantity'] = $product['quantity'];
                $_SESSION['cart'][$productId]['quantity'] = $product['quantity'];
            }
            
            $items[$productId] = $item;
        }
    }
    
    return $items;
}

// Calculate cart total with discounts
function getCartTotal() {
    $items = getCartItems();
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Apply coupon discount if exists
    if (isset($_SESSION['coupon'])) {
        $discount = calculateDiscount($subtotal, $_SESSION['coupon']);
        return $subtotal - $discount;
    }
    
    return $subtotal;
}

// Calculate discount
function calculateDiscount($subtotal, $coupon) {
    if ($coupon['type'] === 'percentage') {
        $discount = $subtotal * ($coupon['value'] / 100);
        return min($discount, $coupon['max_discount'] ?? $discount);
    }
    return min($coupon['value'], $subtotal);
}

// Remove from cart
function removeFromCart($productId) {
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
<?php
// Security functions
function clean($input) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function loginUser($email, $password) {
    global $conn;
    
    $email = clean($email);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
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
            
            // Update last login
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            return true;
        }
    }
    return false;
}

// Product functions
function getProducts($limit = null, $offset = null, $category = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
            
    if ($category) {
        $sql .= " AND (c.id = ? OR c.parent_id = ?)";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $offset = $offset ?: 0;
        $sql .= " LIMIT ?, ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($category && $limit) {
        $stmt->bind_param("iiii", $category, $category, $offset, $limit);
    } elseif ($category) {
        $stmt->bind_param("ii", $category, $category);
    } elseif ($limit) {
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

// Cart functions
function addToCart($productId, $quantity = 1) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        if ($product['quantity'] < $quantity) {
            return false;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $product['sale_price'] ?: $product['price']
            ];
        }
        
        return true;
    }
    
    return false;
}

// Order functions
function createOrder($data) {
    global $conn;
    
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zipcode, shipping_country, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $orderNumber = generateOrderNumber();
        $stmt->bind_param("isdsssssssss", $data['user_id'], $orderNumber, $data['total_amount'], $data['shipping_name'], $data['shipping_email'], $data['shipping_phone'], $data['shipping_address'], $data['shipping_city'], $data['shipping_state'], $data['shipping_zipcode'], $data['shipping_country'], $data['payment_method'], $data['notes']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }
        
        $orderId = $conn->insert_id;
        
        // Insert order items
        foreach ($_SESSION['cart'] as $productId => $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, price, quantity, total) VALUES (?, ?, ?, ?, ?)");
            $total = $item['price'] * $item['quantity'];
            $stmt->bind_param("iidid", $orderId, $productId, $item['price'], $item['quantity'], $total);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order items");
            }
            
            // Update product quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $productId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update product quantity");
            }
        }
        
        $conn->commit();
        unset($_SESSION['cart']);
        
        return $orderId;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        return false;
    }
}

// Helper functions
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function generateOrderNumber() {
    return 'MC' . date('Ymd') . rand(1000, 9999);
}
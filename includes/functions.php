<?php
// User Authentication Functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

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

function registerUser($name, $email, $password) {
    global $conn;
    
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
    return true;
}

function getUserById($id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function emailExists($email) {
    global $conn;
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Product Functions

function getProducts($limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
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

// Cart Functions

function addToCart($productId, $quantity = 1) {
    $product = getProductById($productId);
    
    if (!$product || $product['quantity'] < $quantity) {
        return false;
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    return true;
}

function updateCart($productId, $quantity) {
    $product = getProductById($productId);
    
    if (!$product || $product['quantity'] < $quantity) {
        return false;
    }
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    return true;
}

function removeFromCart($productId) {
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
    
    $items = [];
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = getProductById($productId);
        if ($product) {
            $product['cart_quantity'] = $quantity;
            $items[] = $product;
        }
    }
    
    return $items;
}

function getCartTotal() {
    $total = 0;
    $items = getCartItems();
    
    foreach ($items as $item) {
        $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
        $total += $price * $item['cart_quantity'];
    }
    
    return $total;
}

// Order Functions

function createOrder($data) {
    global $conn;
    
    $conn->begin_transaction();
    
    try {
        // Generate unique order number
        $orderNumber = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        // Insert order
        $sql = "INSERT INTO orders (
            user_id, order_number, total_amount, shipping_name, shipping_email,
            shipping_phone, shipping_address, shipping_city, shipping_state,
            shipping_zipcode, shipping_country, payment_method, notes
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
            throw new Exception("Error creating order");
        }
        
        $orderId = $conn->insert_id;
        
        // Insert order items
        $cartItems = getCartItems();
        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (
                order_id, product_id, name, price, quantity, total
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
            $total = $price * $item['cart_quantity'];
            
            $stmt->bind_param(
                "iisdid",
                $orderId,
                $item['id'],
                $item['name'],
                $price,
                $item['cart_quantity'],
                $total
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating order items");
            }
            
            // Update product quantity
            $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['cart_quantity'], $item['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating product quantity");
            }
        }
        
        $conn->commit();
        clearCart();
        return $orderId;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
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

// Add more helper functions as needed
?>
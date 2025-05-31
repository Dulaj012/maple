<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get all categories for dropdown
$categories = getCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = uploadFile($_FILES['image'], '../assets/images/products/');
        if (!$image) {
            $_SESSION['error_message'] = 'Failed to upload image. Please try again.';
            header('Location: add-product.php');
            exit;
        }
    }

    $data = [
        'category_id' => (int)$_POST['category_id'],
        'name' => clean($_POST['name']),
        'description' => clean($_POST['description']),
        'price' => (float)$_POST['price'],
        'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
        'image' => $image,
        'quantity' => (int)$_POST['quantity'],
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];

    if (addProduct($data)) {
        $_SESSION['success_message'] = 'Product added successfully.';
        header('Location: products.php');
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to add product. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-dashboard">
    <!-- Admin Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Admin Content -->
    <div class="admin-content">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Admin Main -->
        <div class="admin-main">
            <div class="container-fluid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h4>Add New Product</h4>
                        <a href="products.php" class="btn btn-outline-primary">Back to Products</a>
                    </div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="add-product.php" method="post" enctype="multipart/form-data" class="admin-form">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Regular Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sale_price">Sale Price (Optional)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id">Category</label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">Stock Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Product Image</label>
                                    <div class="image-preview">
                                        <img src="../assets/images/placeholder.png" alt="Preview">
                                    </div>
                                    <input type="file" class="form-control image-input" name="image" accept="image/*" required>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                                        <label class="form-check-label" for="is_featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-save">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
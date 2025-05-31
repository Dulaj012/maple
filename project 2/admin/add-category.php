<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = uploadFile($_FILES['image'], '../assets/images/categories/');
        if (!$image) {
            $_SESSION['error_message'] = 'Failed to upload image. Please try again.';
            header('Location: add-category.php');
            exit;
        }
    }

    $data = [
        'name' => clean($_POST['name']),
        'description' => clean($_POST['description']),
        'image' => $image
    ];

    if (addCategory($data)) {
        $_SESSION['success_message'] = 'Category added successfully.';
        header('Location: categories.php');
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to add category. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Admin Dashboard</title>
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
                        <h4>Add New Category</h4>
                        <a href="categories.php" class="btn btn-outline-primary">Back to Categories</a>
                    </div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="add-category.php" method="post" enctype="multipart/form-data" class="admin-form">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Category Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category Image</label>
                                    <div class="image-preview">
                                        <img src="../assets/images/placeholder.png" alt="Preview">
                                    </div>
                                    <input type="file" class="form-control image-input" name="image" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-save">Add Category</button>
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
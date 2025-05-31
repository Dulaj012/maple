<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get category ID
if (!isset($_GET['id'])) {
    header('Location: categories.php');
    exit;
}

$categoryId = (int)$_GET['id'];
$category = getCategoryById($categoryId);

if (!$category) {
    header('Location: categories.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $image = $category['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $newImage = uploadFile($_FILES['image'], '../assets/images/categories/');
        if ($newImage) {
            $image = $newImage;
        } else {
            $_SESSION['error_message'] = 'Failed to upload image. Other changes will still be saved.';
        }
    }

    $data = [
        'name' => clean($_POST['name']),
        'description' => clean($_POST['description']),
        'image' => $image,
        'status' => $_POST['status']
    ];

    if (updateCategory($categoryId, $data)) {
        $_SESSION['success_message'] = 'Category updated successfully.';
        header('Location: categories.php');
        exit;
    } else {
        $_SESSION['error_message'] = 'Failed to update category. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Admin Dashboard</title>
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
                        <h4>Edit Category</h4>
                        <a href="categories.php" class="btn btn-outline-primary">Back to Categories</a>
                    </div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="edit-category.php?id=<?php echo $categoryId; ?>" method="post" enctype="multipart/form-data" class="admin-form">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Category Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $category['name']; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo $category['description']; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category Image</label>
                                    <div class="image-preview">
                                        <?php if ($category['image']): ?>
                                        <img src="../assets/images/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
                                        <?php else: ?>
                                        <img src="../assets/images/placeholder.png" alt="No Image">
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" class="form-control image-input" name="image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-save">Update Category</button>
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
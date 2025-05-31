<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get query parameters
$categorySlug = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 1000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Get products based on filters
$products = [];
$totalProducts = 0;

if (!empty($categorySlug)) {
    $category = getCategoryBySlug($categorySlug);
    if ($category) {
        $products = getProductsByCategory($category['id'], $limit, $offset);
        $totalProducts = getTotalProductsByCategory($category['id']);
        $pageTitle = $category['name'];
    } else {
        header('Location: shop.php');
        exit;
    }
} elseif (!empty($search)) {
    $products = searchProducts($search, $limit, $offset);
    $totalProducts = getTotalProductsBySearch($search);
    $pageTitle = 'Search Results for "' . $search . '"';
} else {
    $products = getProducts($limit, $offset);
    $totalProducts = getTotalProducts();
    $pageTitle = 'All Products';
}

// Apply price filter
if ($minPrice > 0 || $maxPrice < 1000) {
    $filteredProducts = [];
    foreach ($products as $product) {
        $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
        if ($price >= $minPrice && $price <= $maxPrice) {
            $filteredProducts[] = $product;
        }
    }
    $products = $filteredProducts;
}

// Apply sorting
if ($sort === 'price_low') {
    usort($products, function($a, $b) {
        $priceA = $a['sale_price'] ? $a['sale_price'] : $a['price'];
        $priceB = $b['sale_price'] ? $b['sale_price'] : $b['price'];
        return $priceA - $priceB;
    });
} elseif ($sort === 'price_high') {
    usort($products, function($a, $b) {
        $priceA = $a['sale_price'] ? $a['sale_price'] : $a['price'];
        $priceB = $b['sale_price'] ? $b['sale_price'] : $b['price'];
        return $priceB - $priceA;
    });
} elseif ($sort === 'name_asc') {
    usort($products, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} elseif ($sort === 'name_desc') {
    usort($products, function($a, $b) {
        return strcmp($b['name'], $a['name']);
    });
}

// Calculate pagination
$totalPages = ceil($totalProducts / $limit);
$paginationUrl = 'shop.php?';
if (!empty($categorySlug)) {
    $paginationUrl .= 'category=' . $categorySlug . '&';
}
if (!empty($search)) {
    $paginationUrl .= 'search=' . urlencode($search) . '&';
}
if ($minPrice > 0 || $maxPrice < 1000) {
    $paginationUrl .= 'min_price=' . $minPrice . '&max_price=' . $maxPrice . '&';
}
if ($sort !== 'latest') {
    $paginationUrl .= 'sort=' . $sort . '&';
}

// Get all categories for sidebar
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Fruitables</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Shop Section -->
    <section class="shop-section">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <!-- Categories -->
                    <div class="sidebar-widget">
                        <h4>Categories</h4>
                        <ul class="list-unstyled">
                            <?php foreach($categories as $cat): ?>
                            <li class="mb-2">
                                <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="d-flex justify-content-between align-items-center">
                                    <?php echo $cat['name']; ?>
                                    <span class="badge bg-light text-dark"><?php echo getTotalProductsByCategory($cat['id']); ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Price Filter -->
                    <div class="sidebar-widget">
                        <h4>Filter by Price</h4>
                        <form action="shop.php" method="get">
                            <?php if (!empty($categorySlug)): ?>
                            <input type="hidden" name="category" value="<?php echo $categorySlug; ?>">
                            <?php endif; ?>
                            <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo $search; ?>">
                            <?php endif; ?>
                            <?php if ($sort !== 'latest'): ?>
                            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                            <?php endif; ?>
                            
                            <div class="price-filter">
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input">
                                    <input type="range" class="min-range" min="0" max="1000" value="<?php echo $minPrice; ?>" step="5">
                                    <input type="range" class="max-range" min="0" max="1000" value="<?php echo $maxPrice; ?>" step="5">
                                </div>
                                <div class="price-inputs">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="min-input" name="min_price" value="<?php echo $minPrice; ?>">
                                    </div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="max-input" name="max_price" value="<?php echo $maxPrice; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-filter">Filter</button>
                        </form>
                    </div>

                    <!-- Organic Filter -->
                    <div class="sidebar-widget">
                        <h4>Product Type</h4>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="organic">
                            <label class="form-check-label" for="organic">
                                Organic Products
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sale">
                            <label class="form-check-label" for="sale">
                                Sale Products
                            </label>
                        </div>
                    </div>

                    <!-- Best Sellers -->
                    <div class="sidebar-widget">
                        <h4>Best Sellers</h4>
                        <?php
                        $bestSellers = getBestsellerProducts(3);
                        foreach($bestSellers as $product):
                        ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="70" height="70" class="rounded">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6><a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a></h6>
                                <div class="product-price">
                                    <?php if($product['sale_price']): ?>
                                    <span class="current-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php else: ?>
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Shop Topbar -->
                    <div class="shop-topbar">
                        <div class="showing-result">
                            Showing <?php echo min(count($products), $limit); ?> of <?php echo $totalProducts; ?> results
                        </div>
                        <div class="sort-by">
                            <form action="shop.php" method="get" id="sort-form">
                                <?php if (!empty($categorySlug)): ?>
                                <input type="hidden" name="category" value="<?php echo $categorySlug; ?>">
                                <?php endif; ?>
                                <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo $search; ?>">
                                <?php endif; ?>
                                <?php if ($minPrice > 0 || $maxPrice < 1000): ?>
                                <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                                <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                                <?php endif; ?>
                                
                                <label>Sort By:</label>
                                <select name="sort" onchange="document.getElementById('sort-form').submit()">
                                    <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Latest</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="row">
                        <?php if (count($products) > 0): ?>
                            <?php foreach($products as $product): ?>
                                <?php include 'includes/product-card.php'; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">No products found.</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <?php echo generatePagination($page, $totalPages, $paginationUrl); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
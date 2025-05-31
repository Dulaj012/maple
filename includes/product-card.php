<div class="col-md-3 col-sm-6">
    <div class="product-card">
        <div class="product-image">
            <img src="assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            <div class="product-actions">
                <?php if(isLoggedIn()): ?>
                <a href="process/add-to-wishlist.php?id=<?php echo $product['id']; ?>" title="Add to Wishlist"><i class="fas fa-heart"></i></a>
                <?php else: ?>
                <a href="login.php" title="Login to add to Wishlist"><i class="fas fa-heart"></i></a>
                <?php endif; ?>
                <a href="product.php?slug=<?php echo $product['slug']; ?>" title="Quick View"><i class="fas fa-eye"></i></a>
            </div>
        </div>
        <div class="product-info">
            <div class="product-category"><?php echo $product['category_name']; ?></div>
            <h3 class="product-title"><a href="product.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a></h3>
            <div class="product-price">
                <?php if($product['sale_price']): ?>
                <span class="current-price"><?php echo formatPrice($product['sale_price']); ?></span>
                <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                <?php else: ?>
                <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                <?php endif; ?>
            </div>
            <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span>(4.5)</span>
            </div>
            <form action="process/add-to-cart.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="add-to-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
            </form>
        </div>
    </div>
</div>
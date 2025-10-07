<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';
?>
<!-- special deals banner -->
<section class="deals-banner">
    <div class="container">
        <span class="deal-info">Special Deals & Discounts - Up to 30% off!  Limited Time Offer only!</span>
        <a href="featured.php" class="deals-link">Shop Deals</a>
    </div>
</section>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Elevate Your Style</h1>
            <p>Discover the latest trends in fashion clothing, shoes, bags, and watches.</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
        <p><small>Photo by <a href="https://unsplash.com/@priscilladupreez?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Priscilla Du Preez</a> on <a href="https://unsplash.com/photos/brown-and-white-coat-hanged-on-rack-my5cwTzhmNI?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash">Unsplash</a></small></p>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-products">
    <div class="container">
        <div class="section-title">
            <h2>Featured Products</h2>
        </div>
        <div class="product-grid">
            <?php
            // Get featured products from database
            $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="product-card-link">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                            </div>
                            <div class="product-info">
                                <?php
                                // Get category name
                                $cat_sql = "SELECT name FROM categories WHERE category_id = " . $row['category_id'];
                                $cat_result = $conn->query($cat_sql);
                                $cat_row = $cat_result->fetch_assoc();
                                ?>
                                <p class="product-category"><?php echo $cat_row['name']; ?></p>
                                <h3 class="product-title"><?php echo $row['name']; ?></h3>
                                <p class="product-price">$<?php echo number_format($row['price'], 2); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p>No products found</p>";
            }
            ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories">
    <div class="container">
        <div class="section-title">
            <h2>Shop by Category</h2>
        </div>
        <div class="category-grid">
            <?php
            // Get categories from database
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <a href="category.php?id=<?php echo $row['category_id']; ?>" class="category-card">
                        <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        <div class="category-overlay">
                            <h3 class="category-name"><?php echo $row['name']; ?></h3>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p>No categories found</p>";
            }
            ?>
        </div>
    </div>
</section>


<?php
include 'includes/footer.php';
?>

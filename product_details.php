<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Get product details
$sql = "SELECT * FROM products WHERE product_id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();

// Get category name
$cat_sql = "SELECT name FROM categories WHERE category_id = " . $product['category_id'];
$cat_result = $conn->query($cat_sql);
$cat_row = $cat_result->fetch_assoc();
?>

<!-- Product Details Section -->
<section class="product-details">
    <div class="container">
        <div class="product-details-container">
            <div class="product-image-large">
                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
            </div>
            <div class="product-info-detailed">
                <p class="product-category"><?php echo $cat_row['name']; ?></p>
                <h1 class="product-title"><?php echo $product['name']; ?></h1>
                <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo $product['description']; ?></p>
                </div>
                <div class="product-availability">
                    <p><?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?></p>
                </div>
                
                <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <div class="size-selector">
                        <label for="size">Size:</label>
                        <select id="size" name="size" class="size-select" required>
                            <option value="">Select Size</option>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M" selected>M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>
                    
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn decrement">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                            <button type="button" class="quantity-btn increment">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn add-to-cart" data-product-id="<?php echo $product['product_id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo $product['price']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>Add to Cart</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Related Products Section -->
<section class="related-products">
    <div class="container">
        <div class="section-title">
            <h2>Related Products</h2>
        </div>
        <div class="product-grid">
            <?php
            // Get related products from the same category
            $related_sql = "SELECT * FROM products WHERE category_id = {$product['category_id']} AND product_id != {$product['product_id']} LIMIT 4";
            $related_result = $conn->query($related_sql);
            
            if ($related_result->num_rows > 0) {
                while($row = $related_result->fetch_assoc()) {
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo $cat_row['name']; ?></p>
                            <h3 class="product-title"><?php echo $row['name']; ?></h3>
                            <p class="product-price">$<?php echo number_format($row['price'], 2); ?></p>
                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No related products found</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

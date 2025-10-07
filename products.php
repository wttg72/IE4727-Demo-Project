<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Handle pagination
$items_per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Handle category filter
$category_filter = "";
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = (int)$_GET['category'];
    $category_filter = "WHERE category_id = $category_id";
}

// Get total products count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products $category_filter";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $items_per_page);
?>

<!-- Products Header -->
<section class="page-header">
    <div class="container">
        <h1>Our Products</h1>
    </div>
</section>

<!-- Products Section -->
<section class="products-section">
    <div class="container">
        <div class="products-filter">
            <h3>Filter by Category</h3>
            <ul>
                <li><a href="products.php" <?php echo !isset($_GET['category']) ? 'class="active"' : ''; ?>>All Products</a></li>
                <?php
                // Get categories for filter
                $cat_sql = "SELECT * FROM categories";
                $cat_result = $conn->query($cat_sql);
                
                if ($cat_result->num_rows > 0) {
                    while($cat_row = $cat_result->fetch_assoc()) {
                        $active_class = (isset($_GET['category']) && $_GET['category'] == $cat_row['category_id']) ? 'class="active"' : '';
                        echo '<li><a href="products.php?category=' . $cat_row['category_id'] . '" ' . $active_class . '>' . $cat_row['name'] . '</a></li>';
                    }
                }
                ?>
            </ul>
        </div>
        
        <div class="products-display">
            <div class="product-grid">
                <?php
                // Get products from database with pagination
                $sql = "SELECT * FROM products $category_filter ORDER BY created_at DESC LIMIT $offset, $items_per_page";
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
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>" class="pagination-link">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>" class="pagination-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>" class="pagination-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

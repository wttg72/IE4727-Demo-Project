<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$category_id = (int)$_GET['id'];

// Get category details
$cat_sql = "SELECT * FROM categories WHERE category_id = $category_id";
$cat_result = $conn->query($cat_sql);

if ($cat_result->num_rows == 0) {
    header("Location: categories.php");
    exit();
}

$category = $cat_result->fetch_assoc();

// Handle pagination
$items_per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total products count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products WHERE category_id = $category_id";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $items_per_page);
?>

<!-- Category Header -->
<section class="page-header category-header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/<?php echo $category['image']; ?>');">
    <div class="container">
        <h1><?php echo $category['name']; ?></h1>
        <p><?php echo $category['description']; ?></p>
    </div>
</section>

<!-- Products Section -->
<section class="products-section">
    <div class="container">
        <div class="products-display">
            <div class="product-grid">
                <?php
                // Get products from database with pagination
                $sql = "SELECT * FROM products WHERE category_id = $category_id ORDER BY created_at DESC LIMIT $offset, $items_per_page";
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
                                    <p class="product-category"><?php echo $category['name']; ?></p>
                                    <h3 class="product-title"><?php echo $row['name']; ?></h3>
                                    <p class="product-price">$<?php echo number_format($row['price'], 2); ?></p>
                                </div>
                            </div>
                        </a>
                        <?php
                    }
                } else {
                    echo "<p>No products found in this category</p>";
                }
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

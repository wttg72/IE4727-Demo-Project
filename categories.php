<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Get all categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!-- Categories Header -->
<section class="page-header">
    <div class="container">
        <h1>Shop by Categories</h1>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="category-grid-large">
            <?php foreach ($categories as $category): ?>
                <div class="category-card-large">
                    <a href="products.php?category=<?php echo $category['category_id']; ?>">
                        <img src="images/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
                        <div class="category-overlay">
                            <h3 class="category-name"><?php echo $category['name']; ?></h3>
                            <p class="category-description"><?php echo $category['description']; ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

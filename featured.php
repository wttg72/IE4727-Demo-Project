<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// get current date for page generation timestamp
$current_date = date('Y-m-d');
$current_month = date('F');
$current_year = date('Y');

// Get special deals (random products with discount)
$deals_sql = "SELECT p.*, c.name as category_name FROM products p 
             JOIN categories c ON p.category_id = c.category_id 
             ORDER BY RAND() LIMIT 9";
$deals_result = $conn->query($deals_sql);
$deals_products = [];

if ($deals_result->num_rows > 0) {
    while($row = $deals_result->fetch_assoc()) {
        // Calculate a random discount between 10% and 30%
        $discount = rand(10, 30);
        $row['original_price'] = $row['price'];
        $row['price'] = round($row['price'] * (1 - $discount/100), 2);
        $row['discount'] = $discount;
        $deals_products[] = $row;
    }
}
?>

<!-- Special Deals Section -->
<section class="deals-section">
    <div class="container">
        <div class="section-title">
            <h2>Special Deals</h2>
            <p>Limited time offers. Get them while they last!</p>
        </div>
        <div class="deals-grid">
            <?php foreach ($deals_products as $product): ?>
                <div class="deal-card">
                    <div class="discount-badge"><?php echo $product['discount']; ?>% OFF</div>
                    <div class="deal-image">
                        <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="deal-info">
                        <h3 class="deal-title"><?php echo $product['name']; ?></h3>
                        <div class="deal-price">
                            <span class="original-price">$<?php echo number_format($product['original_price'], 2); ?></span>
                            <span class="sale-price">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="deal-timer">
                            <p>Offer ends in: <span id="timer-<?php echo $product['product_id']; ?>">23:59:59</span></p>
                        </div>
                        <a href="product_details.php?id=<?php echo $product['product_id']; ?>" class="btn">Shop Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<script>
    // Simple countdown timers for deals
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($deals_products as $product): ?>
        (function() {
            // Random hours between 1 and 23
            let hours = Math.floor(Math.random() * 23) + 1;
            let minutes = 59;
            let seconds = 59;
            
            const timerId = setInterval(function() {
                seconds--;
                if (seconds < 0) {
                    seconds = 59;
                    minutes--;
                    if (minutes < 0) {
                        minutes = 59;
                        hours--;
                        if (hours < 0) {
                            clearInterval(timerId);
                            hours = 0;
                            minutes = 0;
                            seconds = 0;
                        }
                    }
                }
                
                const timerDisplay = document.getElementById('timer-<?php echo $product['product_id']; ?>');
                if (timerDisplay) {
                    timerDisplay.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        })();
        <?php endforeach; ?>
    });
</script>

<?php
include 'includes/footer.php';
?>

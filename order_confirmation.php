<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id is set in session
if (!isset($_SESSION['order_id'])) {
    header("Location: account.php");
    exit();
}

$order_id = $_SESSION['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order_sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows == 0) {
    header("Location: account.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.name, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);

// Clear the order_id from session
unset($_SESSION['order_id']);
?>

<!-- Order Confirmation Section -->
<section class="page-header">
    <div class="container">
        <h1>Order Confirmation</h1>
    </div>
</section>

<section class="order-confirmation-section">
    <div class="container">
        <div class="order-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been placed successfully. Your order number is: <strong>#<?php echo $order_id; ?></strong></p>
            <p>We have sent an order confirmation to your email address with details about your order and a link to verify the design mockup.</p>
            <p>Please check your email and follow the instructions to verify your design.</p>
        </div>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <div class="order-info">
                <div class="info-item">
                    <span class="label">Order Number:</span>
                    <span class="value">#<?php echo $order_id; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Order Status:</span>
                    <span class="value status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
            </div>
            
            <h3>Order Items</h3>
            <div class="order-items">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        while ($item = $items_result->fetch_assoc()): 
                            $subtotal += $item['price'] * $item['quantity'];
                        ?>
                        <tr>
                            <td class="product-info">
                                <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="order-product-image">
                                <div>
                                    <h4><?php echo $item['name']; ?></h4>
                                </div>
                            </td>
                            <td data-label="Size"><?php echo isset($item['size']) ? $item['size'] : 'M'; ?></td>
                            <td data-label="Price">$<?php echo number_format($item['price'], 2); ?></td>
                            <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                            <td data-label="Total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="order-totals">
                <div class="total-item">
                    <span class="label">Subtotal:</span>
                    <span class="value">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php 
                $shipping = $subtotal > 100 ? 0 : 10;
                ?>
                <div class="total-item">
                    <span class="label">Shipping:</span>
                    <span class="value"><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                </div>
                <div class="total-item grand-total">
                    <span class="label">Total:</span>
                    <span class="value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <div class="shipping-info">
                <h3>Shipping Address</h3>
                <p><?php echo nl2br($order['shipping_address']); ?></p>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ol>
                    <li>Check your email for the order confirmation with a link to verify your design mockup.</li>
                    <li>Review the design mockup and approve it or request changes.</li>
                    <li>Once the design is approved, your order will be processed and shipped.</li>
                    <li>You will receive an email notification when your order ships.</li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn">Continue Shopping</a>
                <a href="account.php" class="btn btn-secondary">View My Account</a>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: login.php?redirect=checkout.php");
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Calculate cart totals
$subtotal = 0;
$shipping = 0;
$total = 0;

// Get cart items from database
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    // extract unique product IDs from cart keys
    $product_ids = [];
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $product_ids[] = $item['product_id'];
    }
    $product_ids = array_unique($product_ids);
    $ids_string = implode(',', array_map('intval', $product_ids));
    
    $sql = "SELECT * FROM products WHERE product_id IN ($ids_string)";
    $result = $conn->query($sql);
    
    // create product lookup array
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[$row['product_id']] = $row;
        }
    }
    
    // build cart items with proper keys
    foreach ($_SESSION['cart'] as $cart_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        if (isset($products[$product_id])) {
            $product = $products[$product_id];
            $quantity = $cart_item['quantity'];
            $size = $cart_item['size'];
            $item_total = $product['price'] * $quantity;
            
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity,
                'size' => $size,
                'total' => $item_total
            ];
            
            $subtotal += $item_total;
        }
    }
    
    // Calculate shipping (simplified for this example)
    $shipping = $subtotal > 100 ? 0 : 10;
    $total = $subtotal + $shipping;
}

// Process checkout form
$shipping_address = $payment_method = "";
$shipping_address_err = $payment_method_err = "";
$order_success = false;
$order_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate shipping address
    if (empty(trim($_POST["shipping_address"]))) {
        $shipping_address_err = "Please enter shipping address";
    } else {
        $shipping_address = trim($_POST["shipping_address"]);
    }
    
    // Validate payment method
    if (empty($_POST["payment_method"])) {
        $payment_method_err = "Please select a payment method";
    } else {
        $payment_method = $_POST["payment_method"];
    }
    
    // Check input errors before processing the order
    if (empty($shipping_address_err) && empty($payment_method_err)) {
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) 
                         VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($order_sql);
            $stmt->bind_param("idss", $user_id, $total, $shipping_address, $payment_method);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // Add order items
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($item_sql);
            
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
                
                // Update product stock (optional)
                $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
                $update_stmt = $conn->prepare($update_stock_sql);
                $update_stmt->bind_param("ii", $item['quantity'], $item['id']);
                $update_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Clear the cart
            $_SESSION['cart'] = [];
            
            // Set success flag
            $order_success = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!-- Checkout Section -->
<section class="page-header">
    <div class="container">
        <h1>Checkout</h1>
    </div>
</section>

<section class="checkout-section">
    <div class="container">
        <?php if ($order_success): ?>
            <div class="order-success">
                <h2>Thank You for Your Order!</h2>
                <p>Your order has been placed successfully. Your order number is: <strong>#<?php echo $order_id; ?></strong></p>
                <p>We have sent an order confirmation to your email address. You will receive another email when your order ships.</p>
                <a href="index.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="checkout-container">
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form action="checkout_process.php" method="post" class="needs-validation">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo $user['name']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control <?php echo (!empty($shipping_address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo empty($shipping_address) ? $user['address'] : $shipping_address; ?></textarea>
                            <?php if (!empty($shipping_address_err)): ?>
                                <div class="error-message"><?php echo $shipping_address_err; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <h2>Payment Method</h2>
                        <div class="form-group">
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" <?php echo ($payment_method == "credit_card") ? "checked" : ""; ?> onchange="togglePaymentFields('credit_card')">
                                    <label for="credit_card">Credit Card</label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="debit_card" name="payment_method" value="debit_card" <?php echo ($payment_method == "debit_card") ? "checked" : ""; ?> onchange="togglePaymentFields('debit_card')">
                                    <label for="debit_card">Debit Card</label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" <?php echo ($payment_method == "bank_transfer") ? "checked" : ""; ?> onchange="togglePaymentFields('bank_transfer')">
                                    <label for="bank_transfer">Bank Transfer</label>
                                </div>
                            </div>
                            <?php if (!empty($payment_method_err)): ?>
                                <div class="error-message"><?php echo $payment_method_err; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="card_details" class="payment-details" style="display: none;">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-row">
                                <div class="form-group half">
                                    <label for="card_expiry">Expiration Date</label>
                                    <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/YY">
                                </div>
                                <div class="form-group half">
                                    <label for="card_cvv">CVV</label>
                                    <input type="text" id="card_cvv" name="card_cvv" class="form-control" placeholder="123">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name" class="form-control">
                            </div>
                        </div>
                        
                        <div id="bank_details" class="payment-details" style="display: none;">
                            <div class="form-group">
                                <p>Please use the following bank details to make your transfer:</p>
                                <p><strong>Bank:</strong> StyleHub Bank</p>
                                <p><strong>Account Number:</strong> 1234567890</p>
                                <p><strong>Sort Code:</strong> 12-34-56</p>
                                <p><strong>Reference:</strong> Your email address</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn checkout-btn">Place Order</button>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <div class="item-info">
                                    <span class="item-quantity"><?php echo $item['quantity']; ?>x</span>
                                    <span class="item-name"><?php echo $item['name']; ?> (Size: <?php echo $item['size']; ?>)</span>
                                </div>
                                <span class="item-price">$<?php echo number_format($item['total'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-totals">
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping:</span>
                            <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free'; ?></span>
                        </div>
                        <div class="summary-item total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function togglePaymentFields(paymentMethod) {
    // Hide all payment details sections
    document.querySelectorAll('.payment-details').forEach(function(el) {
        el.style.display = 'none';
    });
    
    // Show the relevant section based on payment method
    if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
        document.getElementById('card_details').style.display = 'block';
    } else if (paymentMethod === 'bank_transfer') {
        document.getElementById('bank_details').style.display = 'block';
    }
}

// Initialize payment fields on page load
document.addEventListener('DOMContentLoaded', function() {
    const creditCardRadio = document.getElementById('credit_card');
    const debitCardRadio = document.getElementById('debit_card');
    const bankTransferRadio = document.getElementById('bank_transfer');
    
    if (creditCardRadio.checked) {
        togglePaymentFields('credit_card');
    } else if (debitCardRadio.checked) {
        togglePaymentFields('debit_card');
    } else if (bankTransferRadio.checked) {
        togglePaymentFields('bank_transfer');
    }
});
</script>

<?php
include 'includes/footer.php';
?>

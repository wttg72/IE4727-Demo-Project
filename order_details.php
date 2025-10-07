<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// redirect if not logged in or no order id
if (!isset($_SESSION['user_id'])) { header("Location: login.php?redirect=account.php"); exit(); }
if (empty($_GET['id'])) { header("Location: account.php"); exit(); }

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// get order details
$order_result = $conn->query("SELECT *, verification_token, design_approved, design_mockup FROM orders WHERE order_id = $order_id AND user_id = $user_id");
if ($order_result->num_rows == 0) { header("Location: account.php"); exit(); }
$order = $order_result->fetch_assoc();

// get order items with size - need to get all fields to identify unique rows
$items_result = $conn->query("SELECT oi.product_id, oi.quantity, oi.price, oi.size, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = $order_id");
$order_items = [];
$item_index = 0;
while($row = $items_result->fetch_assoc()) { 
    // add a unique index and row identifier for each item
    $row['item_index'] = $item_index++;
    // create a unique row identifier using product_id, price, and current size
    $row['row_id'] = md5($row['product_id'] . '_' . $row['price'] . '_' . ($row['size'] ?? 'M') . '_' . $item_index);
    $order_items[] = $row; 
}

// available sizes
$sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
?>

<!-- Order Details Section -->
<section class="page-header">
    <div class="container">
        <h1>Order Details</h1>
    </div>
</section>

<section class="order-details-section">
    <div class="container">
        <div class="order-details-header">
            <h2>Order #<?php echo $order['order_id']; ?></h2>
            <p>Placed: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
        </div>
        <?php endif; ?>
        
        <div class="order-approval-section">
            <h3>Confirmation</h3>
            <?php if (!$order['design_approved']): ?>
            <form action="approve_order.php" method="post">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <button type="submit" name="action" value="approve" class="btn btn-success">Confirm Order</button>
                <button type="submit" name="action" value="request_changes" class="btn btn-secondary">Request Changes</button>
            </form>
            <?php else: ?>
            <div class="order-confirmed"><p><i class="fas fa-check-circle"></i> Order confirmed</p></div>
            <?php endif; ?>
        </div>
        
        <div class="order-details-content">
            <div class="order-items">
                <h3>Items</h3>
                <div class="table-container">
                    <form id="order-edit-form" action="update_order_items.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <table class="order-items-table">
                            <thead><tr><th>Product</th><th>Size</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td class="product-info">
                                        <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="order-product-image">
                                        <div><h4><?php echo $item['name']; ?></h4></div>
                                    </td>
                                    <td>
                                        <?php if (!$order['design_approved']): ?>
                                        <select name="size[<?php echo $item['item_index']; ?>]" class="size-select">
                                            <?php foreach ($sizes as $size): ?>
                                            <option value="<?php echo $size; ?>" <?php echo (isset($item['size']) && $item['size'] == $size) ? 'selected' : ''; ?>>
                                                <?php echo $size; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="product_id[<?php echo $item['item_index']; ?>]" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="old_size[<?php echo $item['item_index']; ?>]" value="<?php echo isset($item['size']) ? $item['size'] : 'M'; ?>">
                                        <input type="hidden" name="item_price[<?php echo $item['item_index']; ?>]" value="<?php echo $item['price']; ?>">
                                        <?php else: ?>
                                        <span><?php echo isset($item['size']) ? $item['size'] : 'M'; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (!$order['design_approved']): ?>
                        <div class="edit-actions">
                            <button type="submit" class="btn btn-primary">Update Order</button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="order-summary-details">
                <div class="order-totals">
                    <h3>Summary</h3>
                    <div class="summary-item"><span>Subtotal:</span><span>$<?php echo number_format($order['total_amount'] - 10, 2); ?></span></div>
                    <div class="summary-item"><span>Shipping:</span><span>$10.00</span></div>
                    <div class="summary-item total"><span>Total:</span><span>$<?php echo number_format($order['total_amount'], 2); ?></span></div>
                </div>
                
                <div class="shipping-info">
                    <h3>Shipping</h3>
                    <p><?php echo nl2br($order['shipping_address']); ?></p>
                </div>
                
                <div class="payment-info">
                    <h3>Payment</h3>
                    <p>Method: <?php echo str_replace('_', ' ', ucfirst($order['payment_method'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

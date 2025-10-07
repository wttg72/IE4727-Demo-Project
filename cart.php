<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Initialize the shopping cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// migrate old cart structure to new structure
if (!empty($_SESSION['cart'])) {
    $new_cart = [];
    foreach ($_SESSION['cart'] as $key => $item) {
        // check if this is old format (numeric key only)
        if (is_numeric($key) && !isset($item['product_id'])) {
            // old format: key is product_id, item has quantity and size
            $product_id = $key;
            $size = isset($item['size']) ? $item['size'] : 'M';
            $cart_key = $product_id . '_' . $size;
            $new_cart[$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $item['quantity'],
                'size' => $size
            ];
        } else {
            // already new format or has product_id
            $new_cart[$key] = $item;
        }
    }
    $_SESSION['cart'] = $new_cart;
}

// Handle cart actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'remove' && isset($_GET['id'])) {
        $cart_key = $_GET['id'];
        if (isset($_SESSION['cart'][$cart_key])) {
            unset($_SESSION['cart'][$cart_key]);
        }
    } else if ($_GET['action'] == 'update' && isset($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $cart_key => $quantity) {
            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] = max(1, (int)$quantity);
            }
        }
        // update sizes if provided
        if (isset($_POST['size'])) {
            foreach ($_POST['size'] as $cart_key => $new_size) {
                if (isset($_SESSION['cart'][$cart_key])) {
                    $old_item = $_SESSION['cart'][$cart_key];
                    // if size changed, create new cart entry
                    if ($old_item['size'] != $new_size) {
                        $new_cart_key = $old_item['product_id'] . '_' . $new_size;
                        // check if new size already exists
                        if (isset($_SESSION['cart'][$new_cart_key])) {
                            $_SESSION['cart'][$new_cart_key]['quantity'] += $old_item['quantity'];
                        } else {
                            $_SESSION['cart'][$new_cart_key] = [
                                'product_id' => $old_item['product_id'],
                                'quantity' => $old_item['quantity'],
                                'size' => $new_size
                            ];
                        }
                        unset($_SESSION['cart'][$cart_key]);
                    }
                }
            }
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit;
}

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
                'cart_key' => $cart_key,
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
?>

<!-- Cart Section -->
<section class="page-header">
    <div class="container">
        <h1>Your Shopping Cart</h1>
    </div>
</section>

<section class="cart-section">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="products.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <form action="cart.php?action=update" method="post">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="cart-item">
                                    <td class="product-info" data-label="Product">
                                        <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-product-image">
                                        <div>
                                            <h3><?php echo $item['name']; ?></h3>
                                        </div>
                                    </td>
                                    <td class="item-size" data-label="Size">
                                        <select name="size[<?php echo $item['cart_key']; ?>]" class="size-select-cart">
                                            <option value="XS" <?php echo $item['size'] == 'XS' ? 'selected' : ''; ?>>XS</option>
                                            <option value="S" <?php echo $item['size'] == 'S' ? 'selected' : ''; ?>>S</option>
                                            <option value="M" <?php echo $item['size'] == 'M' ? 'selected' : ''; ?>>M</option>
                                            <option value="L" <?php echo $item['size'] == 'L' ? 'selected' : ''; ?>>L</option>
                                            <option value="XL" <?php echo $item['size'] == 'XL' ? 'selected' : ''; ?>>XL</option>
                                            <option value="XXL" <?php echo $item['size'] == 'XXL' ? 'selected' : ''; ?>>XXL</option>
                                        </select>
                                    </td>
                                    <td class="item-price" data-label="Price" data-price="<?php echo $item['price']; ?>">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="item-quantity" data-label="Quantity">
                                        <div class="quantity-controls">
                                            <button type="button" class="quantity-btn decrement">-</button>
                                            <input type="number" name="quantity[<?php echo $item['cart_key']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                            <button type="button" class="quantity-btn increment">+</button>
                                        </div>
                                    </td>
                                    <td class="item-total" data-label="Total">$<?php echo number_format($item['total'], 2); ?></td>
                                    <td class="item-remove" data-label="Action">
                                        <a href="cart.php?action=remove&id=<?php echo $item['cart_key']; ?>" class="remove-item">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <button type="submit" class="btn btn-update">Update Cart</button>
                    </div>
                </form>
                
                <div class="cart-summary">
                    <h3>Order Summary</h3>
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
                        <span class="cart-total">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn checkout-btn">Proceed to Checkout</a>
                    <a href="products.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

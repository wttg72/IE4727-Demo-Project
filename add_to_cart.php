<?php
session_start();

// Check if product_id and quantity are provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
$size = isset($_POST['size']) ? $_POST['size'] : 'M';

// create unique cart key using product_id and size
$cart_key = $product_id . '_' . $size;

// Initialize the shopping cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or update the product in the cart
if (isset($_SESSION['cart'][$cart_key])) {
    // Product with same size already in cart, update quantity
    $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
} else {
    // New product or different size, add to cart
    $_SESSION['cart'][$cart_key] = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'size' => $size
    ];
}

// Redirect back to the product page or to the cart
if (isset($_POST['redirect_to']) && $_POST['redirect_to'] == 'cart') {
    header("Location: cart.php");
} else {
    header("Location: product_details.php?id=$product_id&added=1");
}
exit();
?>

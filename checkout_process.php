<?php
require_once 'includes/db_connect.php';
require_once 'includes/mail.php';
session_start();

// redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php'); exit();
}

// process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // get user info
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
    $user = $user_result->fetch_assoc();
    
    // get form data
    $shipping_address = $conn->real_escape_string($_POST['shipping_address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    
    // redirect if cart empty
    if (empty($_SESSION['cart'])) {
        header('Location: cart.php'); exit();
    }
    
    // calculate total
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $cart_key => $item) {
        $product_id = $item['product_id'];
        $product_result = $conn->query("SELECT price FROM products WHERE product_id = $product_id");
        if ($product_result && $product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();
            $total_amount += $product['price'] * $item['quantity'];
        }
    }
    
    // generate token and create order
    $verification_token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, verification_token) VALUES (?, ?, 'pending', ?, ?, ?)");
    $stmt->bind_param("idsss", $user_id, $total_amount, $shipping_address, $payment_method, $verification_token);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // add order items
        foreach ($_SESSION['cart'] as $cart_key => $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $size = $item['size'];
            $product_stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product = $product_stmt->get_result()->fetch_assoc();
            
            // insert item with size
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");
            $item_stmt->bind_param("iiids", $order_id, $product_id, $quantity, $product['price'], $size);
            $item_stmt->execute();
        }
        
        // create mockups dir if needed
        if (!file_exists('images/mockups')) mkdir('images/mockups', 0777, true);
        
        // set placeholder mockup
        $mockup_stmt = $conn->prepare("UPDATE orders SET design_mockup = 'placeholder-mockup.jpg' WHERE order_id = ?");
        $mockup_stmt->bind_param("i", $order_id);
        $mockup_stmt->execute();
        
        // send email and mark as sent
        if (createOrderConfirmationEmail($order_id, $user)) {
            $email_stmt = $conn->prepare("UPDATE orders SET email_sent = TRUE WHERE order_id = ?");
            $email_stmt->bind_param("i", $order_id);
            $email_stmt->execute();
        }
        
        // clear cart and redirect
        unset($_SESSION['cart']);
        $_SESSION['order_id'] = $order_id;
        header('Location: order_confirmation.php');
        exit();
    } else {
        $_SESSION['error'] = "Error creating order: " . $conn->error;
        header('Location: checkout.php');
        exit();
    }
}
?>

<?php
require_once 'includes/db_connect.php';
session_start();

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    
    // check if sizes were submitted
    if (!isset($_POST['size']) || empty($_POST['size'])) {
        $_SESSION['error_message'] = "No sizes to update.";
        header("Location: order_details.php?id=$order_id");
        exit();
    }
    
    $sizes = $_POST['size'];
    $product_ids = $_POST['product_id'];
    $old_sizes = $_POST['old_size'];
    $item_prices = $_POST['item_price'];
    
    // verify the order belongs to the current user and is not approved yet
    $order_check = "SELECT * FROM orders WHERE order_id = ? AND user_id = ? AND design_approved = 0";
    $stmt = $conn->prepare($order_check);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // check if size column exists, if not add it
        $check_column = $conn->query("SHOW COLUMNS FROM order_items LIKE 'size'");
        if ($check_column->num_rows == 0) {
            // add size column if it doesn't exist
            $conn->query("ALTER TABLE order_items ADD COLUMN size VARCHAR(10) DEFAULT 'M' AFTER quantity");
        }
        
        // update sizes for each order item - use old_size and price to identify specific row
        $updated_count = 0;
        $errors = [];
        foreach ($sizes as $index => $new_size) {
            $product_id = $product_ids[$index];
            $old_size = $old_sizes[$index];
            $price = $item_prices[$index];
            
            // update only the specific row matching product_id, old size, and price
            $update_sql = "UPDATE order_items SET size = ? WHERE product_id = ? AND order_id = ? AND price = ? AND (size = ? OR (size IS NULL AND ? = 'M')) LIMIT 1";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("siidss", $new_size, $product_id, $order_id, $price, $old_size, $old_size);
                if ($update_stmt->execute()) {
                    if ($update_stmt->affected_rows > 0) {
                        $updated_count++;
                    }
                } else {
                    $errors[] = "Failed to update product $product_id: " . $update_stmt->error;
                }
            } else {
                $errors[] = "Failed to prepare statement: " . $conn->error;
            }
        }
        
        if ($updated_count > 0) {
            $_SESSION['success_message'] = "Order updated successfully! ($updated_count items updated)";
        } else {
            $_SESSION['error_message'] = "No items were updated. " . implode(", ", $errors);
        }
    } else {
        $_SESSION['error_message'] = "Cannot update order. Order may be already confirmed or does not exist.";
    }
    
    // redirect back to order details
    header("Location: order_details.php?id=$order_id");
    exit();
} else {
    // if accessed directly without form submission, redirect to account page
    header("Location: account.php");
    exit();
}
?>

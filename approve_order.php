<?php
require_once 'includes/db_connect.php';
require_once 'includes/mail.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $user_id = $_SESSION['user_id'];
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Verify the order belongs to the current user
    $order_check = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt = $conn->prepare($order_check);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Handle the action
        if ($action == 'approve') {
            // Update order status to confirmed and mark as approved
            $update_sql = "UPDATE orders SET status = 'confirmed', design_approved = 1 WHERE order_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $order_id);
            
            if ($update_stmt->execute()) {
                // Get user info for email
                $user_query = "SELECT * FROM users WHERE user_id = ?";
                $user_stmt = $conn->prepare($user_query);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user = $user_result->fetch_assoc();
                
                // Send confirmation email
                $subject = "Your StyleHub Order #$order_id is Confirmed";
                $body = "<h2>Thank you for confirming your order!</h2>";
                $body .= "<p>Dear {$user['name']},</p>";
                $body .= "<p>Your order #$order_id has been confirmed and is now being processed.</p>";
                $body .= "<p>You can view your order details at any time by logging into your account.</p>";
                $body .= "<p>Thank you for shopping with StyleHub!</p>";
                
                sendEmail($user['email'], $subject, $body);
                
                // Set success message
                $_SESSION['success_message'] = "Your order has been confirmed. Thank you!";
            } else {
                $_SESSION['error_message'] = "Error updating order status. Please try again.";
            }
        } elseif ($action == 'request_changes') {
            // Redirect to change request form
            header("Location: request_changes.php?order_id=$order_id");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Invalid order.";
    }
    
    // Redirect back to order details
    header("Location: order_details.php?id=$order_id");
    exit();
} else {
    // If accessed directly without form submission, redirect to account page
    header("Location: account.php");
    exit();
}
?>

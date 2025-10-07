<?php
// mail.php - combined email handling functions

function sendEmail($to, $subject, $body) {
    // headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: StyleHub <f31ee@localhost>\r\n";
    $headers .= "Reply-To: f31ee@localhost\r\n";
    
    // send to f32ee@localhost
    $result = mail('f32ee@localhost', $subject, $body, $headers, '-ff31ee@localhost');

    return $result;
}

function createOrderConfirmationEmail($order_id, $user) {
    global $conn;
    
    // check for template
    $template_exists = false;
    if (isset($conn)) {
        $template_result = $conn->query("SELECT * FROM email_templates WHERE template_name = 'order_confirmation'");
        if ($template_result && $template_result->num_rows > 0) {
            $template = $template_result->fetch_assoc();
            $template_exists = true;
        }
    }
    
    // create subject
    $subject = $template_exists 
        ? str_replace('{order_id}', $order_id, $template['subject'])
        : "Your StyleHub Order Confirmation - #$order_id";
    
    // create body
    if ($template_exists) {
        $body = str_replace('{customer_name}', $user['name'], $template['body']);
        $body = str_replace('{order_id}', $order_id, $body);
    } else {
        $body = "<h2>Thank you for your order!</h2>";
        $body .= "<p>Dear {$user['name']},</p>";
        $body .= "<p>We are pleased to confirm your order #$order_id has been received and is being processed.</p>";
        $body .= "<p>Please review your order details by clicking the link below:</p>";
    }
    
    // add order link
    $order_link = "http://" . $_SERVER['HTTP_HOST'] . "/IE4727/Project/order_details.php?id=$order_id";
    
    if ($template_exists) {
        $body = str_replace('{order_link}', $order_link, $body);
        $body = str_replace('{verification_link}', $order_link, $body);
    } else {
        $body .= "<p><a href=\"$order_link\">View and Confirm Your Order</a></p>";
        $body .= "<p>Please review your order details and confirm your order.</p>";
        $body .= "<p>Thank you for shopping with StyleHub!</p>";
    }
    
    return sendEmail($user['email'], $subject, $body);
}
?>

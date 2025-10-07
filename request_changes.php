<?php
require_once 'includes/db_connect.php';
require_once 'includes/mail.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: account.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the current user
$order_check = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($order_check);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: account.php");
    exit();
}

$order = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $changes_requested = $conn->real_escape_string($_POST['changes_requested']);
    
    // Update order status to indicate changes requested
    $update_sql = "UPDATE orders SET status = 'changes_requested' WHERE order_id = ?";
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
        
        // Send notification email to admin
        $subject = "Order Change Request - Order #$order_id";
        $body = "<h2>Order Change Request</h2>";
        $body .= "<p>Customer: {$user['name']}</p>";
        $body .= "<p>Order ID: $order_id</p>";
        $body .= "<p>Changes Requested:</p>";
        $body .= "<div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>";
        $body .= "<pre>$changes_requested</pre>";
        $body .= "</div>";
        
        // Send to admin (in this case, to f32ee@localhost)
        sendEmail("admin@stylehub.com", $subject, $body);
        
        // Set success message
        $_SESSION['success_message'] = "Your change request has been submitted. We'll get back to you soon.";
        
        // Redirect back to order details
        header("Location: order_details.php?id=$order_id");
        exit();
    } else {
        $error_message = "Error submitting your request. Please try again.";
    }
}
?>

<!-- Request Changes Section -->
<section class="page-header">
    <div class="container">
        <h1>Request Changes to Order #<?php echo $order_id; ?></h1>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="" method="post">
                <div class="form-group">
                    <label for="changes_requested">Please describe the changes you would like to make to your order:</label>
                    <textarea id="changes_requested" name="changes_requested" class="form-control" rows="8" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Submit Change Request</button>
                    <a href="order_details.php?id=<?php echo $order_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.form-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}
</style>

<?php
include 'includes/footer.php';
?>

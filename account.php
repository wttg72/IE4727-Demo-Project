<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=account.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Handle profile update
$name = $phone = $address = "";
$name_err = $phone_err = $address_err = "";
$update_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", trim($_POST["name"]))) {
        $name_err = "Name should contain only letters and spaces";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number";
    } elseif (!preg_match("/^\d{8,12}$/", str_replace(['-', ' '], '', trim($_POST["phone"])))) {
        $phone_err = "Please enter a valid phone number";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate address
    if (empty(trim($_POST["address"]))) {
        $address_err = "Please enter your address";
    } elseif (strlen(trim($_POST["address"])) < 10) {
        $address_err = "Please enter a complete address";
    } else {
        $address = trim($_POST["address"]);
    }
    
    // Check input errors before updating the database
    if (empty($name_err) && empty($phone_err) && empty($address_err)) {
        
        // Prepare an update statement
        $sql = "UPDATE users SET name = ?, phone = ?, address = ? WHERE user_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $param_name, $param_phone, $param_address, $param_id);
            
            // Set parameters
            $param_name = $name;
            $param_phone = $phone;
            $param_address = $address;
            $param_id = $user_id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Update session variable
                $_SESSION['name'] = $name;
                
                // Update user data for display
                $user['name'] = $name;
                $user['phone'] = $phone;
                $user['address'] = $address;
                
                $update_success = true;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
}

// Get user orders
$orders_sql = "SELECT *, design_approved FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = $conn->query($orders_sql);
$orders = [];

if ($orders_result->num_rows > 0) {
    while($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!-- Account Section -->
<section class="page-header">
    <div class="container">
        <h1>My Account</h1>
    </div>
</section>

<section class="account-section">
    <div class="container">
        <div class="account-tabs">
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="profile">Profile</button>
                <button class="tab-btn" data-tab="orders">Order History</button>
            </div>
            
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane active" id="profile">
                    <h2>My Profile</h2>
                    
                    <?php if ($update_success): ?>
                        <div class="success-message">
                            <p>Your profile has been updated successfully.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                <small>Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $user['name']; ?>">
                                <?php if (!empty($name_err)): ?>
                                    <div class="error-message"><?php echo $name_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $user['phone']; ?>">
                                <?php if (!empty($phone_err)): ?>
                                    <div class="error-message"><?php echo $phone_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $user['address']; ?></textarea>
                                <?php if (!empty($address_err)): ?>
                                    <div class="error-message"><?php echo $address_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" class="btn">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="tab-pane" id="orders">
                    <h2>Order History</h2>
                    
                    <?php if (empty($orders)): ?>
                        <p>You haven't placed any orders yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <?php 
                                                // Check if design is approved - if yes, it's confirmed
                                                if (isset($order['design_approved']) && $order['design_approved'] == 1): 
                                                ?>
                                                <span class="order-status confirmed" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">Confirmed</span>
                                                <?php elseif (!empty($order['status'])): ?>
                                                <span class="order-status <?php echo strtolower($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="order-status pending">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn-small">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and panes
                tabBtns.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to current button and pane
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>

<?php
include 'includes/footer.php';
?>

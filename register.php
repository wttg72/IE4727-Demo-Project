<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

$name = $email = $phone = $address = $password = $confirm_password = "";
$name_err = $email_err = $phone_err = $address_err = $password_err = $confirm_password_err = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", trim($_POST["name"]))) {
        $name_err = "Name should contain only letters and spaces";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address";
    } else {
        // Check if email already exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
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
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match";
        }
    }
    
    // Check input errors before inserting into database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($address_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $param_name, $param_email, $param_password, $param_phone, $param_address);
            
            // Set parameters
            $param_name = $name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_phone = $phone;
            $param_address = $address;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $registration_success = true;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
}
?>

<!-- Registration Section -->
<section class="page-header">
    <div class="container">
        <h1>Create an Account</h1>
    </div>
</section>

<section class="registration-section">
    <div class="container">
        <?php if ($registration_success): ?>
            <div class="success-message">
                <h2>Registration Successful!</h2>
                <p>Your account has been created successfully. You can now <a href="login.php">login</a> to your account.</p>
            </div>
        <?php else: ?>
            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <?php if (!empty($name_err)): ?>
                            <div class="error-message"><?php echo $name_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <?php if (!empty($email_err)): ?>
                            <div class="error-message"><?php echo $email_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                        <?php if (!empty($phone_err)): ?>
                            <div class="error-message"><?php echo $phone_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $address; ?></textarea>
                        <?php if (!empty($address_err)): ?>
                            <div class="error-message"><?php echo $address_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <?php if (!empty($password_err)): ?>
                            <div class="error-message"><?php echo $password_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <?php if (!empty($confirm_password_err)): ?>
                            <div class="error-message"><?php echo $confirm_password_err; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

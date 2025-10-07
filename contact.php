<?php
require_once 'includes/db_connect.php';
require_once 'includes/mail.php';
include 'includes/header.php';

$name = $email = $subject = $message = "";
$name_err = $email_err = $subject_err = $message_err = "";
$submission_success = false;

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
        $email = trim($_POST["email"]);
    }
    
    // Validate subject
    if (empty(trim($_POST["subject"]))) {
        $subject_err = "Please enter a subject";
    } else {
        $subject = trim($_POST["subject"]);
    }
    
    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message";
    } else {
        $message = trim($_POST["message"]);
    }
    
    // Check input errors before processing the form
    if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)) {
        // Prepare email content
        $to = 'info@stylehub.com.sg'; // Change this to your admin email
        $email_subject = "Contact Form: $subject";
        
        // Create HTML email body
        $email_body = "<h2>New Contact Form Submission</h2>";
        $email_body .= "<p><strong>Name:</strong> $name</p>";
        $email_body .= "<p><strong>Email:</strong> $email</p>";
        $email_body .= "<p><strong>Subject:</strong> $subject</p>";
        $email_body .= "<p><strong>Message:</strong></p>";
        $email_body .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        
        // Create plain text version
        $alt_body = "New Contact Form Submission\n";
        $alt_body .= "Name: $name\n";
        $alt_body .= "Email: $email\n";
        $alt_body .= "Subject: $subject\n";
        $alt_body .= "Message:\n$message";
        
        // Send email using our mailer utility
        $email_sent = sendEmail($to, $email_subject, $email_body);
        
        // Send confirmation email to the user
        $confirmation_subject = "Thank you for contacting StyleHub";
        $confirmation_body = "<h2>Thank You for Contacting Us</h2>";
        $confirmation_body .= "<p>Dear $name,</p>";
        $confirmation_body .= "<p>We have received your message and will get back to you as soon as possible.</p>";
        $confirmation_body .= "<p>Here's a summary of your inquiry:</p>";
        $confirmation_body .= "<p><strong>Subject:</strong> $subject</p>";
        $confirmation_body .= "<p><strong>Message:</strong></p>";
        $confirmation_body .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        $confirmation_body .= "<p>Best regards,<br>The StyleHub Team</p>";
        
        // Send confirmation email
        sendEmail($email, $confirmation_subject, $confirmation_body);
        
        $submission_success = true;
        
        // Reset form fields
        $name = $email = $subject = $message = "";
    }
}
?>

<!-- Contact Section -->
<section class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-info">
            <div class="contact-details">
                <h2>Get in Touch</h2>
                <p>Have questions about our products or services? Fill out the form and we'll get back to you as soon as possible.</p>
                <div class="contact-item">
                    <h3>Email</h3>
                    <p>info@stylehub.com.sg</p>
                </div>
                <div class="contact-item">
                    <h3>Phone</h3>
                    <p>+65 6123 4567</p>
                </div>
                <div class="contact-item">
                    <h3>Address</h3>
                    <p>123 Orchid Street 1, Orchid Mall, S(512345)</p>
                </div>
                <div class="contact-item">
                    <h3>Business Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p>Saturday: 10:00 AM - 4:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>
            
            <div class="form-container">
                <?php if ($submission_success): ?>
                    <div class="success-message">
                        <h2>Thank You!</h2>
                        <p>Your message has been sent successfully. We'll get back to you soon.</p>
                    </div>
                <?php else: ?>
                    <h2>Send Us a Message</h2>
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
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control <?php echo (!empty($subject_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $subject; ?>">
                            <?php if (!empty($subject_err)): ?>
                                <div class="error-message"><?php echo $subject_err; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>" rows="5"><?php echo $message; ?></textarea>
                            <?php if (!empty($message_err)): ?>
                                <div class="error-message"><?php echo $message_err; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>

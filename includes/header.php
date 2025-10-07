<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleHub - Fashion E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Style<span>Hub</span></a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <?php
                    session_start();
                    if(isset($_SESSION['user_id'])) {
                        echo '<li><a href="account.php"><i class="fas fa-user"></i></a></li>';
                        echo '<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>';
                    } else {
                        echo '<li><a href="login.php"><i class="fas fa-sign-in-alt"></i></a></li>';
                        echo '<li><a href="register.php"><i class="fas fa-user-plus"></i></a></li>';
                    }
                    ?>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i></a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>

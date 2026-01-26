<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>

<header>
    <div class="logo-area">
        <a href="../index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
            <img src="../assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
            <span class="logo-text">KUEMS</span>
        </a>
    </div>
    <nav>
            <a href="../event/event-dashboard.php">Events</a>
            <a href="../uems/about.php">About</a>
            <a href="../event/create-event.php">Registration</a>
            <a href="../uems/contact.php">Contact</a>
        </nav>
</header>

<section class="hero-section">
    <div class="login-card">

        <div class="login-info">
            <h2>Login to Your Account</h2>
            <p>Your credentials are required to continue.</p>
            <div class="register-link">
                Don't have an account? <a href="../register/">Register</a>
            </div>
        </div>

        <div class="login-form-container">
            <form id="loginForm">
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn-login">Login Account</button>
            </form>

            <!-- Add a place to show errors -->
            <div id="errorMessage" style="color: red; margin-top: 10px;"></div>
                    </div>

    </div>
</section>
<script src="../assets/js/script.js"></script>
    <?php include "../includes/footer.php"; ?>
</body>
</html>

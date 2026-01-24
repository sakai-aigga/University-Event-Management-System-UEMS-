<?php
<<<<<<< HEAD
include "../includes/db-config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email            = $_POST['email'];
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT u_id, name, password, role FROM user WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($u_id, $name, $hashedPassword, $role);
    $stmt->fetch();

    if ($u_id && password_verify($password, $hashedPassword)) {
        $_SESSION['u_id'] = $u_id;
        $_SESSION['role'] = $role;
        echo "Login successful";
    } else {
        echo "Invalid credentials";
    }

    $stmt->close();
}
=======
session_start();
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
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
<<<<<<< HEAD
        <img src="cllg.png" class="header-logo" alt="KUEMS">
        <span class="logo-text">KUEMS</span>
    </div>
=======
        <a href="../index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
            <img src="../assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
            <span class="logo-text">KUEMS</span>
        </a>
    </div>
    <nav>
            <a href="#">Events</a>
            <a href="#">About</a>
            <a href="#">Blog</a>
            <a href="#">Registration</a>
            <a href="#">Contact</a>
            
    </nav>
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
</header>

<section class="hero-section">
    <div class="login-card">

        <div class="login-info">
<<<<<<< HEAD
            <h2>Login Your Account</h2>
=======
            <h2>Login to Your Account</h2>
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
            <p>Your credentials are required to continue.</p>
            <div class="register-link">
                Don't have an account? <a href="../register/">Register</a>
            </div>
        </div>

        <div class="login-form-container">
<<<<<<< HEAD
            <form method="POST" action="../api/login/index.php">

                <div class="form-group">
                    <label>Email address *</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" placeholder="Confirm password" required>
                </div>

                <button type="submit" class="btn-login">Login Account</button>
            </form>
        </div>

    </div>
</section>

=======
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
<footer>
    <div class="footer-grid">
        <div class="footer-logo">
            <h2>KUEMS</h2>
            <p>A dedicated event management system for university students to find and host incredible experiences.</p>
        </div>
        <div class="footer-links">
            <h4>Location</h4>
            <ul>
                <li>Hattiban, Lalitpur, Nepal
KUSOED</li>
            </ul>
        </div>
        <div class="footer-links">
            <h4>Contact</h4>
            <ul>
                <li>admin@kusoed.edu.np</li>
                <li>15314105, 15912524</li>
            </ul>
        </div>
    </div>
    <p style="text-align:center; font-size:12px; opacity:0.5; margin-top:30px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1);">
        © 2025 KUEMS. All rights reserved.
    </p>
</footer>
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
</body>
</html>

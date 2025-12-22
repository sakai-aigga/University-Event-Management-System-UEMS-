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
            <a href="#">Events</a>
            <a href="#">About</a>
            <a href="#">Blog</a>
            <a href="#">Registration</a>
            <a href="#">Contact</a>
            <a href="login/" class="btn-login">Login</a>
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
</body>
</html>

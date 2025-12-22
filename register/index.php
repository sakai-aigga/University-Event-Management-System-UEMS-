<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>KUEMS - Register Individual Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<style></style>
    <header>
        <div class="logo-area">
        <a href=# style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
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
        <div class="register-card">
            <div class="register-info">
                <h2>Register Individual Account!</h2>
                <p>For the purpose of industry regulation, your details are required.</p>
                <div class="login-link-alt">
                    Already have an Account? <a href="../login/">Click Here to Login</a>
                </div>
            </div>

            <div class="form-outer-box">
                <form id="registerForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="full_name" placeholder="Enter name here" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter email address" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contact</label>
                        <div class="input-wrapper">
                            <input type="text" name="contact" placeholder="Enter contact number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Create Password</label>
                        <div class="input-wrapper pass-container">
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <span class="show-toggle togglePassword">Show</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper pass-container">
                            <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                            <span class="show-toggle togglePassword">Show</span>
                        </div>
</div>
                    <button type="submit" class="btn-register">Register Account</button>
                    <div id="errorMessage" style="color: red; margin-top: 10px; text-align: center;"></div>

                </form>
            </div>
        </div>
    </section>

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

    <script src="..\assets\js\script.js"></script>
</body>
</html>
<?php
// Optional: Add logic later for errors or redirects
// For now, pure HTML + minimal PHP safety
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>KUEMS - Register Individual Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="register.css">
</head>
<body>

    <header>
        <div class="logo-area">
            <img src="cllg.png" alt="KUEMS" class="header-logo">
            <span class="logo-text">KUEMS</span>
        </div>
        <nav>
            <a href="../">Events</a>
            <a href="../about.php">About</a>
            <a href="../blog.php">Blog</a>
            <a href="../contact.php">Contact</a>
            <a href="../login.php" class="btn-login-nav">Login</a>
        </nav>
    </header>

    <section class="hero-section">
        <div class="register-card">
            <div class="register-info">
                <h2>Register Individual Account!</h2>
                <p>For the purpose of industry regulation, your details are required.</p>
                <div class="login-link-alt">
                    Already have an Account? <a href="../login.php">Click Here to Login</a>
                </div>
            </div>

            <div class="form-outer-box">
                <form method="POST" action="../api/register.php" id="registerForm">
                    <div class="form-group">
                        <label>Full Name*</label>
                        <div class="input-wrapper">
                            <input type="text" name="full_name" placeholder="Enter name here" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address*</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter email address" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Create Password*</label>
                        <div class="input-wrapper pass-container">
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <span class="show-toggle" id="togglePassword">Show</span>
                        </div>
                    </div>

                    <div class="terms-row">
                        <div class="terms-check">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">I agree to terms & conditions</label>
                        </div>
                        <!-- Toggle switch kept for UI consistency -->
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-register">Register Account</button>
                    
                    <div class="divider">Or</div>

                    <button type="button" class="btn-google">
                        <img src="google.png" width="18" alt="Google">
                        Register with Google
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-grid">
            <div class="footer-logo">
                <h2>KUEMS</h2>
                <p>Eventick is a global self-service ticketing platform for live experiences that allows anyone to create, share, find and attend events that fuel their passions.</p>
            </div>
            <div>
                <h4>Location</h4>
                <ul>
                    <li>Hattiban, Lalitpur, Nepal</li>
                    <li>KUSOED</li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li>15314105, 15912524</li>
                    <li>admin@kusoed.edu.np</li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="..\assets\js\script.js"></script>
</body>
</html>
<?php
session_start();
//Redirect to different page if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin-panel/");
    } else {
        header("Location: ../index.php");
    }
    exit();
}


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

    <?php include "../includes/header.php"; ?> 
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
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter email address" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper pass-container">
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
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

<?php
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
        <img src="../assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
        <span class="logo-text">KUEMS</span>
    </div>
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
            <form method="POST" action="../api/login/index.php">

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
        </div>

    </div>
</section>

</body>
</html>

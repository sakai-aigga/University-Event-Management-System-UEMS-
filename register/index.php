<?php
include "../includes/db-config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name             = $_POST['name'];
    $email            = $_POST['email'];
    $contact          = $_POST['contact'];
    $role             = $_POST['role'];
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO user (name, email, password, contact, role) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $contact, $role);
    $stmt->execute();

    echo "Registration successful";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS Register</title>
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
            <h2>Create Your Account</h2>
            <p>Fill in your details to register.</p>
            <div class="register-link">
                Already have an account? <a href="../login/">Login</a>
            </div>
        </div>

        <div class="login-form-container">
            <form method="POST" action="../api/register/index.php">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Enter full name" required>
                </div>

                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label>Contact</label>
                    <input type="text" name="contact" placeholder="Enter contact" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" placeholder="Confirm password" required>
                </div>

                <button type="submit" class="btn-login">Register Account</button>
            </form>
        </div>

    </div>
</section>

</body>
</html>

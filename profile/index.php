<?php
session_start();
if (!isset($_SESSION['u_id'])) {
    header("Location: ../login/");
    exit;
}
$name = $_SESSION['name'];
$email = $_SESSION['email'] ;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include "../includes/header.php"; ?> 

    <!-- Hero / Profile Section -->
    <section class="hero-section">
        <div class="register-card">
            <!-- Left Info -->
            <div class="register-info">
                <h2>Hello, <?= htmlspecialchars($_SESSION['name']) ?></h2>
                <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>
                <a href="logout.php">
                    <button class="btn-login">LOG OUT</button>
                </a>
            </div>
        </div>
    </section>
    <?php include "../includes/footer.php"; ?>

</body>
</html>

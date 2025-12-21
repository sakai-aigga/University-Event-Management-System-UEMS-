<?php
session_start();
if (!isset($_SESSION['u_id'])) {
    header("Location: ../login/");
    exit;
}

include "../includes/db-config.php";
$stmt = $conn->prepare("SELECT name, email, contact FROM user WHERE u_id = ?");
$stmt->bind_param("i", $_SESSION['u_id']);
$stmt->execute();
$stmt->bind_result($name, $email, $contact);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KUEMS</title>
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

<div class="profile-card">
    <div class="profile-info">
        <h2>My Profile</h2>
        <div class="profile-row">
            <span class="profile-label">Name:</span>
            <span class="profile-value"><?= htmlspecialchars($name) ?></span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Email:</span>
            <span class="profile-value"><?= htmlspecialchars($email) ?></span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Contact:</span>
            <span class="profile-value"><?= htmlspecialchars($contact) ?></span>
        </div>
    </div>

    <a href="logout.php" class="btn-logout">Logout</a>
</div>

</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

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
            <a href="#">Registration</a>
            <a href="#">Contact</a>
            <?php if (isset($_SESSION['u_id'])): ?>
                <a href="profile/">👤 <?= $_SESSION['name']; ?></a>
                <a href="profile/logout.php">Logout</a>
            <?php else: ?>
                <a href="login/" class="btn-login">Login</a>
            <?php endif; ?>
        </nav>
    </header>
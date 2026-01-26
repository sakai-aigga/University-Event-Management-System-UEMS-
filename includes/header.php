<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/path-config.php';
?>

<header>
    <div class="logo-area">
    <a href="<?= BASE_URL ?>/index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
        <img src="<?= BASE_URL ?>/assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
        <span class="logo-text">KUEMS</span>
    </a>
</div>

    <nav>
        <a href="<?= BASE_URL ?>/event/event-dashboard.php">Events</a>
        <a href="<?= BASE_URL ?>/uems/about.php">About</a>
        <a href="<?= BASE_URL ?>/event/create-event.php">Registration</a>
        <a href="<?= BASE_URL ?>/uems/contact.php">Contact</a>
    </nav>
</header>

    
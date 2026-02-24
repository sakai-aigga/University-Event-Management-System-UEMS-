<?php
session_start();
require_once 'includes/db-config.php';
$isLoggedIn = isset($_SESSION['u_id']);
$hostEventUrl = $isLoggedIn ? 'uems/contact.php' : 'login/';
$seeEventUrl = 'event/event-dashboard.php';

// Fetch Upcoming Events with registration and count check
$u_id = isset($_SESSION['u_id']) ? $_SESSION['u_id'] : 0;
$upcoming_sql = "SELECT e.*, 
                 r_check.reg_id as is_registered,
                 (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
                 FROM event e 
                 LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
                 WHERE e.is_published = 1 AND e.event_date >= CURDATE() 
                 ORDER BY e.event_date ASC LIMIT 3";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch Past Events with registration and count check
$past_sql = "SELECT e.*, 
             r_check.reg_id as is_registered,
             (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
             FROM event e 
             LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
             WHERE e.is_published = 1 AND e.event_date < CURDATE() 
             ORDER BY e.event_date DESC LIMIT 2";
$past_result = $conn->query($past_sql);

$cat_map = [1=>'Academic', 2=>'Workshop', 3=>'Sports', 4=>'Cultural'];
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS - Home | Discover Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo-area">
            <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
                <img src="assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
                <span class="logo-text">KUEMS</span>
            </a>
        </div>
        <nav>
                <a href="event/event-dashboard.php">Events</a>
                <a href="uems/about.php">About</a>
                <a href="uems/contact.php">Contact</a>
                <?php if (isset($_SESSION['u_id'])): ?>
                <a href="profile/">👤 <?= htmlspecialchars($_SESSION['name']); ?></a>
                <?php else: ?>
                <a href="login/" class="head-btn-login">Login</a>
                <?php endif; ?>
            </nav>
    </header>

    <main>
        <section>
            <div class="section-header">
                <h2>Featured Events</h2>
                <div class="filters">
                </div>
            </div>

            <div class="events-grid">
                <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
                    <?php while($row = $upcoming_result->fetch_assoc()): ?>
                        <?php include "event/event_card.php"; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">No upcoming events at the moment.</p>
                <?php endif; ?>
            </div>
            <br><br>
            <a href="<?= htmlspecialchars($seeEventUrl) ?>">
            <button class="btn-all-events">See All Events</button>
            </a>

        </section>

        <section class="cta-section">
            <div class="cta-banner">
                <img src="https://cdn-icons-png.flaticon.com/512/4341/4341134.png" width="150" alt="CTA">
                <div class="cta-content">
                    <h3>Want to Host Your Loving Event?</h3>
                    <p>Contact us Now and Register your event and reach thousands of students.</p>
                </div>
                <a href="<?= htmlspecialchars($hostEventUrl) ?>" class="btn-pink" style="text-decoration: none; display: inline-block; color: white;">Host An Event</a>
            </div>
        </section>

        <section class="past-events-section">
            <div class="section-header">
                <h2>Past Successful Events</h2>
            </div>
            <div class="events-grid">
                <?php if ($past_result && $past_result->num_rows > 0): ?>
                    <?php while($row = $past_result->fetch_assoc()): ?>
                        <?php include "event/event_card.php"; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">No past events to display.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <style>
        ul{
            list-style-type: none;
        }
    </style>
    <?php include "./includes/footer.php"; ?>
    <?php include "event/event_popup.php"; ?>
</body>
</html>
<?php
session_start();
require_once 'includes/db-config.php';
$isLoggedIn = isset($_SESSION['u_id']);
$hostEventUrl = $isLoggedIn ? 'uems/contact.php' : 'login/';

// Fetch Upcoming Events with registration and count check
$u_id = isset($_SESSION['u_id']) ? $_SESSION['u_id'] : 0;
$upcoming_sql = "SELECT e.*, 
                 r_check.reg_id as is_registered,
                 (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
                 FROM event e 
                 LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
                 WHERE e.is_published = 1 AND e.event_date >= CURDATE() 
                 ORDER BY e.event_date ASC";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch Past Events with registration and count check
$past_sql = "SELECT e.*, 
             r_check.reg_id as is_registered,
             (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
             FROM event e 
             LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
             WHERE e.is_published = 1 AND e.event_date < CURDATE() 
             ORDER BY e.event_date DESC";
$past_result = $conn->query($past_sql);

$cat_map = [1=>'Academic', 2=>'Workshop', 3=>'Sports', 4=>'Cultural'];
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS - Discover All Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "includes/header.php"; ?>

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
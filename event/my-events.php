<?php
session_start();
include '../includes/db-config.php';

$message = "";

// Registration is now handled in event-registration.php

// Fetch Registered Events for the logged-in user
$u_id = isset($_SESSION['u_id']) ? $_SESSION['u_id'] : 0;

if ($u_id > 0) {
    // Upcoming Registered Events
    $upcoming_sql = "SELECT e.*, 
                     r_check.reg_id as is_registered,
                     (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
                     FROM event e 
                     INNER JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
                     WHERE e.is_published = 1 AND e.event_date >= CURDATE() 
                     ORDER BY e.event_date ASC";
    $upcoming_result = $conn->query($upcoming_sql);

    // Past Registered Events
    $past_sql = "SELECT e.*, 
                 r_check.reg_id as is_registered,
                 (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
                 FROM event e 
                 INNER JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
                 WHERE e.is_published = 1 AND e.event_date < CURDATE() 
                 ORDER BY e.event_date DESC";
    $past_result = $conn->query($past_sql);
} else {
    // If not logged in, no registered events to show
    $upcoming_result = false;
    $past_result = false;
}

$cat_map = [1=>'General', 2=>'Academic', 3=>'Sports', 4=>'Cultural'];
?>

<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registered Events - UEMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
    <body>
        <?php include "../includes/header.php"; ?> 

        <main>
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <section>
            <div class="section-header">
                <h2>My Registered Events</h2>
            </div>

            <div class="events-grid">
                <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
                    <?php while($row = $upcoming_result->fetch_assoc()): ?>
                        <?php include "event_card.php"; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">You haven't registered for any events.</p>
                <?php endif; ?>
            </div>
            
        </section>
        <section>
            <div class="section-header">
                <h2>My Past Events</h2>
            </div>
            
            <div class="events-grid">
                 <?php if ($past_result && $past_result->num_rows > 0): ?>
                    <?php while($row = $past_result->fetch_assoc()): ?>
                        <?php include "event_card.php"; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">No registered past events found.</p>
                <?php endif; ?>
            </div>      
        </section>
        </main>
        
        <?php include "../includes/footer.php"; ?> 

        <?php include "event_popup.php"; ?>
    </body>
</html>
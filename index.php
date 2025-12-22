<?php
session_start();
$isLoggedIn = isset($_SESSION['u_id']);
$hostEventUrl = $isLoggedIn ? 'create-event.php' : 'login/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUEMS - Home | Discover Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <header>
        <div class="logo-area">
        <a href=# style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;">
            <img src="assets/images/UEMS_logo.png" class="header-logo" alt="KUEMS">
            <span class="logo-text">KUEMS</span>
        </a>
    </div>
        <nav>
            <a href="#">Events</a>
            <a href="#">About</a>
            <a href="#">Blog</a>
            <a href="#">Registration</a>
            <a href="#">Contact</a>
            <a href="login/" class="btn-login">Login</a>
        </nav>
    </header>

    <main>
        <section>
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <div class="filters">
                </div>
            </div>

            <div class="events-grid">
                <div class="event-card">
                    <span class="free-badge">FREE</span>
                    <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=500" class="event-img">
                    <div class="event-content">
                        <p class="date-tag">NOV 11</p>
                        <h3 class="event-title">Civil Festum</h3>
                        <p class="event-location">📍 University Auditorium</p>
                    </div>
                </div>
                <div class="event-card">
                    <span class="free-badge">FREE</span>
                    <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=500" class="event-img">
                    <div class="event-content">
                        <p class="date-tag">NOV 14</p>
                        <h3 class="event-title">Yoga Festum</h3>
                        <p class="event-location">📍 Campus Ground</p>
                    </div>
                </div>
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&q=80&w=500" class="event-img">
                    <div class="event-content">
                        <p class="date-tag">NOV 25</p>
                        <h3 class="event-title">Night of Melodies</h3>
                        <p class="event-location">📍 Main Stage</p>
                    </div>
                </div>
            </div>
            <br><br>
            <div class="see-all-container">
                <button class="btn-outline">See All Events</button>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-banner">
                <img src="https://cdn-icons-png.flaticon.com/512/4341/4341134.png" width="150" alt="CTA">
                <div class="cta-content">
                    <h3>Add Your Loving Event</h3>
                    <p>Register your event and reach thousands of students.</p>
                </div>
                <a href="<?= htmlspecialchars($hostEventUrl) ?>" class="btn-pink" style="text-decoration: none; display: inline-block; color: white;">Host An Event</a>
            </div>
        </section>

        <section class="past-events-section">
            <div class="section-header">
                <h2>Past Successful Events</h2>
            </div>
            <div class="events-grid">
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&q=80&w=500" class="event-img">
                    <div class="event-content">
                        <h3 class="event-title">Conference Keynote</h3>
                        <p class="event-desc">Successful 2024 edition with over 500+ attendees.</p>
                    </div>
                </div>
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1540575861501-7c00117fc3f3?auto=format&fit=crop&q=80&w=500" class="event-img">
                    <div class="event-content">
                        <h3 class="event-title">Digital Marketing Workshop</h3>
                        <p class="event-desc">Industry experts sharing insights on growth.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <style>
        ul{
            list-style-type: none;
        }
    </style>
    <footer>
    <div class="footer-grid">
        <div class="footer-logo">
            <h2>KUEMS</h2>
            <p>A dedicated event management system for university students to find and host incredible experiences.</p>
        </div>
        <div class="footer-links">
            <h4>Location</h4>
            <ul>
                <li>Hattiban, Lalitpur, Nepal
KUSOED</li>
            </ul>
        </div>
        <div class="footer-links">
            <h4>Contact</h4>
            <ul>
                <li>admin@kusoed.edu.np</li>
                <li>15314105, 15912524</li>
            </ul>
        </div>
    </div>
    <p style="text-align:center; font-size:12px; opacity:0.5; margin-top:30px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1);">
        © 2025 KUEMS. All rights reserved.
    </p>
</footer>

</body>
</html>
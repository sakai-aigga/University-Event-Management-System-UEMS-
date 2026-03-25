<?php
session_start();
require_once 'includes/db-config.php';
$isLoggedIn = isset($_SESSION['u_id']);
$hostEventUrl = $isLoggedIn ? 'uems/contact.php' : 'login/';

// Fetch Departments for filtering
$dept_list = [];
$d_res = $conn->query("SELECT * FROM departments ORDER BY dept_name ASC");
if ($d_res) while($r = $d_res->fetch_assoc()) $dept_list[] = $r;

// Filtering Logic
$f_dept = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : null;
$f_cat  = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$filter_query = "";
if ($f_dept) $filter_query .= " AND e.dept_id = $f_dept";
if ($f_cat)  $filter_query .= " AND e.category_id = $f_cat";

// Fetch Upcoming Events with registration and count check
$u_id = isset($_SESSION['u_id']) ? $_SESSION['u_id'] : 0;
$upcoming_sql = "SELECT e.*, 
                 r_check.reg_id as is_registered,
                 (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
                 FROM event e 
                 LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
                 WHERE e.is_published = 1 AND e.event_date >= CURDATE() $filter_query
                 ORDER BY e.event_date ASC";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch Past Events with registration and count check
$past_sql = "SELECT e.*, 
             r_check.reg_id as is_registered,
             (SELECT COUNT(*) FROM registration r2 WHERE r2.event_id = e.event_id) as current_participants
             FROM event e 
             LEFT JOIN registration r_check ON e.event_id = r_check.event_id AND r_check.u_id = $u_id
             WHERE e.is_published = 1 AND e.event_date < CURDATE() $filter_query
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
    <link rel="icon" href="assets/images/UEMS_logo.png" type="image/x-icon">
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
                    <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                        <div class="select-wrapper">
                            <select name="dept_id" onchange="this.form.submit()" class="filter-select">
                                <option value="">All Departments</option>
                                <?php foreach ($dept_list as $d): ?>
                                    <option value="<?= $d['dept_id'] ?>" <?= $f_dept == $d['dept_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['dept_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="select-wrapper">
                            <select name="category_id" onchange="this.form.submit()" class="filter-select">
                                <option value="">All Categories</option>
                                <?php foreach ($cat_map as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $f_cat == $id ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if($f_dept || $f_cat): ?>
                            <a href="index.php" class="filter-clear">Clear <i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
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
                <a href="<?= htmlspecialchars($hostEventUrl) ?>" class="btn-pink">Host An Event</a>
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
        .filter-select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: white;
            font-size: 14px;
            color: #444;
            cursor: pointer;
            outline: none;
            transition: 0.3s;
        }
        .filter-select:hover, .filter-select:focus {
            border-color: var(--pink-accent);
        }
        .filter-clear {
            font-size: 14px;
            color: #666;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .filter-clear:hover {
            background: rgba(0,0,0,0.05);
            color: var(--pink-accent);
        }
    </style>
    <?php include "./includes/footer.php"; ?>
    <?php include "event/event_popup.php"; ?>
</body>
</html>
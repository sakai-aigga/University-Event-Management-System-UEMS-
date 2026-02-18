<?php
session_start();
include '../includes/db-config.php';

$message = "";

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    if (!isset($_SESSION['u_id'])) {
        header("Location: ../login/");
        exit;
    }

    $event_id = $_POST['event_id'];
    $u_id = $_SESSION['u_id'];

    // Check if already registered
    // Table: registration (reg_id, u_id, event_id, reg_date, attendance_status)
    $check_sql = "SELECT * FROM registration WHERE event_id = ? AND u_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $event_id, $u_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "You are already registered for this event.";
    } else {
        $attendance_status = 'Pending';
        $reg_sql = "INSERT INTO registration (event_id, u_id, attendance_status) VALUES (?, ?, ?)";
        $reg_stmt = $conn->prepare($reg_sql);
        $reg_stmt->bind_param("iis", $event_id, $u_id, $attendance_status);
        if ($reg_stmt->execute()) {
            $message = "Successfully registered for the event!";
        } else {
            $message = "Error registering: " . $reg_stmt->error;
        }
    }
}

// Fetch Upcoming Events
// Table: event
$upcoming_sql = "SELECT * 
                 FROM event 
                 WHERE is_published = 1 AND event_date >= CURDATE() 
                 ORDER BY event_date ASC";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch Past Events
$past_sql = "SELECT * 
             FROM event 
             WHERE is_published = 1 AND event_date < CURDATE() 
             ORDER BY event_date DESC";
$past_result = $conn->query($past_sql);
?>

<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - UEMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .btn-register {
            background-color: #28a745;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-register:hover {
            background-color: #218838;
        }
        .event-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .event-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .event-details {
            margin-bottom: 10px;
            font-size: 0.9em;
            color: #666;
        }
        .no-events {
            grid-column: 1 / -1;
            text-align: center;
            padding: 20px;
            color: #777;
        }
    </style>
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
                <h2>Upcoming Events</h2>
            </div>

            <div class="events-grid">
                <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
                    <?php while($row = $upcoming_result->fetch_assoc()): ?>
                        <div class="event-card">
                            <!-- Helper mapping for category display -->
                            <?php 
                                $cat_map = [1=>'General', 2=>'Academic', 3=>'Sports', 4=>'Cultural'];
                                $cat_name = isset($cat_map[$row['category_id']]) ? $cat_map[$row['category_id']] : 'Event';
                            ?>
                            <span class="free-badge"><?php echo $cat_name; ?></span>
                            <!-- Placeholder image -->
                            <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&q=80&w=500" class="event-img">
                            <div class="event-content">
                                <p class="date-tag"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
                                <h3 class="event-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="event-location">📍 <?php echo htmlspecialchars($row['venue']); ?></p>
                                <div class="event-details">
                                    <p><?php echo substr(htmlspecialchars($row['description']), 0, 80) . '...'; ?></p>
                                    <p>Max: <?php echo $row['max_participants']; ?></p>
                                </div>
                                
                                <?php if (isset($_SESSION['u_id'])): ?>
                                    <form method="POST">
                                        <input type="hidden" name="event_id" value="<?php echo $row['event_id']; ?>">
                                        <button type="submit" name="register" class="btn-register">Register</button>
                                    </form>
                                <?php else: ?>
                                    <a href="../login/" class="btn-register" style="text-align:center; display:block; text-decoration:none; background-color: #007bff;">Login to Register</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">No upcoming events found.</p>
                <?php endif; ?>
            </div>
            
        </section>
        <section>
            <div class="section-header">
                <h2>Past Events</h2>
            </div>
            
            <div class="events-grid">
                 <?php if ($past_result && $past_result->num_rows > 0): ?>
                    <?php while($row = $past_result->fetch_assoc()): ?>
                        <div class="event-card">
                            <?php 
                                $cat_map = [1=>'General', 2=>'Academic', 3=>'Sports', 4=>'Cultural'];
                                $cat_name = isset($cat_map[$row['category_id']]) ? $cat_map[$row['category_id']] : 'Event';
                            ?>
                            <span class="free-badge"><?php echo $cat_name; ?></span>
                            <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&q=80&w=500" class="event-img">
                            <div class="event-content">
                                <p class="date-tag"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
                                <h3 class="event-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p class="event-location">📍 <?php echo htmlspecialchars($row['venue']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-events">No past events found.</p>
                <?php endif; ?>
            </div>      
        </section>
        </main>
        
        <?php include "../includes/footer.php"; ?> 

    </body>
</html>
<?php
require_once '../includes/db-config.php';
require_once 'header.php';

// Fetch stats
$event_count = $conn->query("SELECT COUNT(*) FROM event")->fetch_row()[0];
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
?>

<div class="row">
    <div class="col-lg-6 col-12">
        <div class="small-box admin-stat-primary">
            <div class="inner">
                <h3><?= $event_count ?></h3>
                <p>Manage Events</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar"></i>
            </div>
            <a href="events/index.php" class="small-box-footer">View All Events
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-6 col-12">
        <div class="small-box admin-stat-warning">
            <div class="inner">
                <h3><?= $user_count ?></h3>
                <p>User Registrations</p>
            </div>
            <div class="icon">
                <i class="fas fa-users admin-stat-icon-dim"></i>
            </div>
            <a href="users/index.php" class="small-box-footer">Manage Users
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
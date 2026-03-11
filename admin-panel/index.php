<?php
require_once '../includes/db-config.php';
require_once 'header.php';

// Fetch stats
$event_count = $conn->query("SELECT COUNT(*) FROM event WHERE event_date >= CURDATE()")->fetch_row()[0];
$user_count  = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

// Fetch unread notifications count + latest ones
$notif_count  = 0;
$notif_latest = [];
$notif_res    = @$conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
if ($notif_res) $notif_count = $notif_res->fetch_row()[0];

$notif_list = @$conn->query("SELECT * FROM contact_submissions WHERE is_read = 0 ORDER BY submitted_at DESC LIMIT 5");
if ($notif_list) {
    while ($r = $notif_list->fetch_assoc()) $notif_latest[] = $r;
}
?>

<!-- Stat Cards Row -->
<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box admin-stat-primary">
            <div class="inner">
                <h3><?= $event_count ?></h3>
                <p>Active Events</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
            <a href="events/index.php" class="small-box-footer">View All Events <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-4 col-12">
        <div class="small-box admin-stat-warning">
            <div class="inner">
                <h3><?= $user_count ?></h3>
                <p>User Registrations</p>
            </div>
            <div class="icon"><i class="fas fa-users admin-stat-icon-dim"></i></div>
            <a href="users/index.php" class="small-box-footer">Manage Users <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-4 col-12">
        <div class="small-box admin-stat-notif">
            <div class="inner">
                <h3><?= $notif_count ?></h3>
                <p>Unread Inquiries</p>
            </div>
            <div class="icon"><i class="fas fa-bell admin-stat-icon-dim"></i></div>
            <a href="notifications.php" class="small-box-footer">View Notifications <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Unread Notifications Card (only shown if there are unread) -->
<?php if ($notif_count > 0): ?>
<div class="row mt-2">
    <div class="col-12">
        <div class="card admin-card">
            <div class="card-header admin-card-header-flex">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-2" style="color: var(--pink-accent);"></i>
                    New Inquiries
                    <span class="badge badge-danger ml-2"><?= $notif_count ?> New</span>
                </h3>
                <a href="notifications.php" class="btn btn-sm" style="background: var(--primary-gradient); color: white;">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($notif_latest as $notif): ?>
                        <li class="list-group-item d-flex align-items-start py-3" style="border-left: 4px solid var(--pink-accent);">
                            <div class="notif-icon-circle mr-3" style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--pink-accent),#ff4d4d);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-envelope" style="color:white;font-size:16px;"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong style="color:var(--text-dark);"><?= htmlspecialchars($notif['name'] ?: 'Unknown User') ?></strong>
                                    <small class="text-muted ml-2" style="white-space:nowrap;"><?= date('d/m/Y g:i A', strtotime($notif['submitted_at'])) ?></small>
                                </div>
                                <p class="mb-0 text-muted" style="font-size:0.9rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?= htmlspecialchars(substr($notif['message'] ?? '', 0, 100)) ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
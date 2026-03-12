<?php
include "../../includes/db-config.php";
session_start();

header("Content-Type: application/json");

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$unread_count = 0;
$recent_notifications = [];

// Get actual unread count
$count_sql = $conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
if ($count_sql) {
    $unread_count = (int)$count_sql->fetch_row()[0];
}

// Fetch 5 most recent
$notif_sql = "SELECT id, name, message, submitted_at FROM contact_submissions WHERE is_read = 0 ORDER BY submitted_at DESC LIMIT 5";
$notif_res = $conn->query($notif_sql);

if ($notif_res) {
    while($row = $notif_res->fetch_assoc()) {
        $row['message_short'] = strlen($row['message']) > 50 ? substr($row['message'], 0, 50) . '...' : $row['message'];
        $recent_notifications[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "unread_count" => $unread_count,
    "recent" => $recent_notifications
]);
?>

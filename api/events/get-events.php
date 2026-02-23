<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include '../../includes/db-config.php';

// Fetch all events from the 'event' table
$sql = "SELECT * FROM event WHERE is_published = 1 ORDER BY event_date ASC";
$result = $conn->query($sql);

$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode([
    "success" => true,
    "events" => $events
]);
?>
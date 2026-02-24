<?php
include '../includes/db-config.php';
session_start();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

if (!isset($_SESSION['u_id'])) {
    echo json_encode(["success" => false, "message" => "Authentication required."]);
    exit;
}

$u_id = $_SESSION['u_id'];
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($event_id === 0) {
    echo json_encode(["success" => false, "message" => "Invalid event."]);
    exit;
}

// Perform Unregistration
$del_sql = "DELETE FROM registration WHERE event_id = ? AND u_id = ?";
$stmt = $conn->prepare($del_sql);
$stmt->bind_param("ii", $event_id, $u_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Unregistered successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "You were not registered for this event."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Database error during unregistration."]);
}

$stmt->close();
$conn->close();

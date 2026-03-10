<?php
session_start();
include '../includes/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u_id = $_SESSION['u_id'];

    $stmt = $conn->prepare("UPDATE users SET profile_image = NULL WHERE u_id = ?");
    $stmt->bind_param("i", $u_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

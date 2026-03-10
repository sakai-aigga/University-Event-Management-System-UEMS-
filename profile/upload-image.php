<?php
session_start();
include '../includes/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_image"])) {
    $u_id = $_SESSION['u_id'];
    $file = $_FILES["profile_image"];

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds maximum limit.']);
        exit;
    }

    if ($file['error'] === UPLOAD_ERR_OK) {
        $maxSize = 16 * 1024 * 1024; // 16MB for medium blob
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 16MB limit.']);
            exit;
        }

        $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp', 'avif');
        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Only JPG, PNG, JPEG, GIF, and WEBP are allowed.']);
            exit;
        }

        $imageData = file_get_contents($file['tmp_name']);

        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE u_id = ?");
        $null = NULL;
        $stmt->bind_param("bi", $null, $u_id);
        $stmt->send_long_data(0, $imageData);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading file. Error code: ' . $file['error']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
}

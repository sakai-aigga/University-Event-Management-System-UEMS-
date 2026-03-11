<?php
session_start();
include '../includes/db-config.php';

if (!isset($_SESSION['u_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$u_id = $_SESSION['u_id'];

// Don't allow admin deletion via this endpoint (safety check)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
     header('Content-Type: application/json');
     echo json_encode(['success' => false, 'message' => 'Admin accounts cannot be deleted here.']);
     exit;
}

// 1. Delete user registrations first to avoid foreign key constraints
$del_reg = $conn->prepare("DELETE FROM registration WHERE u_id = ?");
$del_reg->bind_param("i", $u_id);
$del_reg->execute();
$del_reg->close();

// 2. Delete the user account
$del_user = $conn->prepare("DELETE FROM users WHERE u_id = ?");
$del_user->bind_param("i", $u_id);

$response = ['success' => false, 'message' => ''];

if ($del_user->execute()) {
    session_destroy();
    $response['success'] = true;
    $response['message'] = "Your account has been permanently deleted.";
} else {
    $response['message'] = "An error occurred while deleting your account. Please try again.";
}

$del_user->close();

header('Content-Type: application/json');
echo json_encode($response);
exit;

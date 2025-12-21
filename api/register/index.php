<?php
include "../../includes/db-config.php";

header("Content-Type: application/json");

// Allow only POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$contact  = $_POST['contact'] ?? '';
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
// Basic validation
if (
    empty($name) || empty($email) || empty($contact) ||
    empty($password) || empty($confirm)
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if ($password !== $confirm) {
    echo json_encode([
        "success" => false,
        "message" => "Passwords do not match"
    ]);
    exit;
}

// Check if email exists
$check = $conn->prepare(
    "SELECT u_id FROM user WHERE email = ? LIMIT 1"
);
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email already registered"
    ]);
    exit;
}
$check->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare(
    "INSERT INTO user (name, email, password, contact)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $hashedPassword,
    $contact,
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Registration successful"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Registration failed"
    ]);
}

$stmt->close();
$conn->close();

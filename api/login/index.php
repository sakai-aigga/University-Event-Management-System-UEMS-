<?php
include "../../includes/db-config.php";
session_start();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare(
    "SELECT password FROM users WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();

if ($hashedPassword && password_verify($password, $hashedPassword)) {
    $_SESSION['username'] = $username;

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "username" => $username
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
}

$stmt->close();
$conn->close();

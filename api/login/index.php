<?php
include "../../includes/db-config.php";
session_start();

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

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT u_id, name, password FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($u_id, $name, $hashedPassword);
$stmt->fetch();

if ($u_id && password_verify($password, $hashedPassword)) {

    $_SESSION['u_id']  = $u_id;
    $_SESSION['name']  = $name;

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "users" => [
            "u_id"  => $u_id,
            "name"  => $name,
            "email" => $email,
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
}

$stmt->close();
$conn->close();

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
$role = $_POST['role'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

// Fetch user with role from DB
$stmt = $conn->prepare(
    "SELECT u_id, name, password, role FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($u_id, $name, $hashedPassword, $role);
$stmt->fetch();

if ($u_id && password_verify($password, $hashedPassword)) {

    // Save info in session
    $_SESSION['u_id']  = $u_id;
    $_SESSION['name']  = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role']  = $role;

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "role" => $role,
        "user" => [
            "u_id"  => $u_id,
            "name"  => $name,
            "email" => $email,
            "role"  => $role
        ]
    ]);
    $stmt->close();
    $conn->close();
    exit;
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

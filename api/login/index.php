<?php
include "../../includes/db-config.php";
session_start();

header("Content-Type: application/json");

<<<<<<< HEAD
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
=======
// Allow only POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

<<<<<<< HEAD
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
=======
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
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
<<<<<<< HEAD
        "user" => [
            "username" => $username
=======
        "users" => [
            "u_id"  => $u_id,
            "name"  => $name,
            "email" => $email,
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
<<<<<<< HEAD
        "message" => "Invalid credentials"
=======
        "message" => "Invalid email or password"
>>>>>>> d0c69e3dc617b67a68153a9ab340951899fd1b0b
    ]);
}

$stmt->close();
$conn->close();

<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "uems";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    // Return error as JSON so Flutter can show it
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "DB Connection failed: " . $conn->connect_error]);
    exit;
}
?>
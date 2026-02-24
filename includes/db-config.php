<?php
// --- Add these lines to fix connection issues ---
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit; // Handle preflight requests
    }
}
// ------------------------------------------------

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
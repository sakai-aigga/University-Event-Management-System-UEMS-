<?php
ob_start(); // Enable output buffering for non-blocking response
include "../includes/db-config.php";
include "../includes/mail-helper.php";

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

// Ensure session is started for user tracking
session_start();

// Redirect or error if not logged in (now required)
if (!isset($_SESSION['u_id'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Please login to send a message."
    ]);
    exit;
}

$user_id = $_SESSION['u_id'];

// Create table if not exists for tracking
$sql_create = "CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    message TEXT NULL,
    is_read TINYINT(1) DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_create);

// Attempt to add columns if they don't exist (fails silently if they do)
@$conn->query("ALTER TABLE contact_submissions ADD COLUMN name VARCHAR(255) AFTER user_id");
@$conn->query("ALTER TABLE contact_submissions ADD COLUMN email VARCHAR(255) AFTER name");
@$conn->query("ALTER TABLE contact_submissions ADD COLUMN message TEXT AFTER email");
@$conn->query("ALTER TABLE contact_submissions ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER message");

// No cooldown or limit checks - message sent instantly without waiting constraints

// Get POST data
$message = $_POST['message'] ?? '';

// Fetch name and email from logged in user automatically
$name = '';
$email = '';
$stmt_user = $conn->prepare("SELECT name, email FROM users WHERE u_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($name, $email);
$stmt_user->fetch();
$stmt_user->close();

// Basic validation
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

// Save to database FIRST (instant) so user gets immediate feedback
$stmt = $conn->prepare("INSERT INTO contact_submissions (user_id, name, email, message, is_read) VALUES (?, ?, ?, ?, 0)");
$stmt->bind_param("isss", $user_id, $name, $email, $message);
$stmt->execute();

// Return success immediately to the user
$response = json_encode(["success" => true, "message" => "Message sent successfully"]);

// Set headers to close connection immediately so browser doesn't wait for SMTP
header('Content-Length: ' . strlen($response));
header('Connection: close');
echo $response;

// Flush all output buffers to send response to browser NOW
ob_end_flush();
@ob_flush();
flush();

// Detach from request if possible
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
if (function_exists('litespeed_finish_request')) {
    litespeed_finish_request();
}

// Close session so it doesn't block other requests
session_write_close();

// Now attempt to send the email in the background (after response is sent)
$subject = "New Contact Form Submission from " . $name;
$emailBody = "
    <h2>Contact Form Submission</h2>
    <p><strong>Name:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Message:</strong></p>
    <p>" . nl2br(htmlspecialchars($message)) . "</p>
";

// Best-effort email send - won't block the user
@sendMail(RECEIVER_EMAIL, $subject, $emailBody, $email);
?>

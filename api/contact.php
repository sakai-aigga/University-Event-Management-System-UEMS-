<?php
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

// 1. Check for cooldown (5 minutes)
$cooldown_seconds = 300; // 5 minutes
$cooldown_check = "SELECT MAX(submitted_at) as last_submission FROM contact_submissions WHERE user_id = $user_id";
$res_cooldown = $conn->query($cooldown_check);
if ($res_cooldown && $row = $res_cooldown->fetch_assoc()) {
    if ($row['last_submission']) {
        $last_time = strtotime($row['last_submission']);
        $diff = time() - $last_time;
        if ($diff < $cooldown_seconds) {
            $wait_seconds = $cooldown_seconds - $diff;
            $wait_minutes = ceil($wait_seconds / 60);
            $unit = $wait_minutes === 1 ? 'minute' : 'minutes';
            echo json_encode([
                "success" => false,
                "message" => "Please wait {$wait_minutes} {$unit} before sending another message."
            ]);
            exit;
        }
    }
}

// 2. Check for message limit (e.g., 5 per day)
$limit_check = "SELECT COUNT(*) as total FROM contact_submissions WHERE user_id = $user_id AND submitted_at > NOW() - INTERVAL 1 DAY";
$res_limit = $conn->query($limit_check);
if ($res_limit && $row = $res_limit->fetch_assoc()) {
    if ($row['total'] >= 2) {
        echo json_encode([
            "success" => false, 
            "message" => "You have reached your daily limit of 2 messages. Please try again tomorrow."
        ]);
        exit;
    }
}

// Get POST data
$name = $_POST['name'] ?? '';
$message = $_POST['message'] ?? '';

// Fetch email from logged in user since it's hidden from form
$email = '';
$stmt_email = $conn->prepare("SELECT email FROM users WHERE u_id = ?");
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$stmt_email->bind_result($email);
$stmt_email->fetch();
$stmt_email->close();

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

// Prepare email content
$subject = "New Contact Form Submission from " . $name;
$emailBody = "
    <h2>Contact Form Submission</h2>
    <p><strong>Name:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Message:</strong></p>
    <p>" . nl2br(htmlspecialchars($message)) . "</p>
";

// Send the mail to the receiver defined in config
$result = sendMail(RECEIVER_EMAIL, $subject, $emailBody, $email);

if ($result['success']) {
    // Log the submission with actual data
    $stmt = $conn->prepare("INSERT INTO contact_submissions (user_id, name, email, message, is_read) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("isss", $user_id, $name, $email, $message);
    $stmt->execute();
}

echo json_encode($result);
?>

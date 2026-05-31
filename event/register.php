<?php
include '../includes/db-config.php';
session_start();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$u_id = null;
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($event_id === 0) {
    echo json_encode(["success" => false, "message" => "Invalid event."]);
    exit;
}

// 1. Authentication Check (Login if needed)
if (!isset($_SESSION['u_id'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Login required to register."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT u_id, name, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_uid, $name, $hashedPassword, $role);
    $stmt->fetch();
    $stmt->close();

    if ($db_uid && password_verify($password, $hashedPassword)) {
        $_SESSION['u_id']  = $db_uid;
        $_SESSION['name']  = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role']  = $role;
        $u_id = $db_uid;
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password."]);
        exit;
    }
} else {
    $u_id = $_SESSION['u_id'];
}

// 2. Fetch Event Data with Locking
$conn->begin_transaction();

try {
    $event_sql = "SELECT event_date, max_participants, 
                 (SELECT COUNT(*) FROM registration WHERE event_id = ?) as current_participants 
                 FROM event WHERE event_id = ? FOR UPDATE"; // Row-level lock
    $stmt = $conn->prepare($event_sql);
    $stmt->bind_param("ii", $event_id, $event_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $event = $res->fetch_assoc();
    $stmt->close();

    if (!$event) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Event not found."]);
        exit;
    }

    if (strtotime($event['event_date']) < strtotime(date('Y-m-d'))) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Registration is closed for past events."]);
        exit;
    }

    if ($event['max_participants'] > 0 && $event['current_participants'] >= $event['max_participants']) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Event is fully booked."]);
        exit;
    }

// 3. Duplicate Check
$check_sql = "SELECT reg_id FROM registration WHERE event_id = ? AND u_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $event_id, $u_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    $check_stmt->close();
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "You are already registered."]);
    exit;
}
$check_stmt->close();

// 4. Perform Registration
    $attendance_status = 'Pending';
    $reg_sql = "INSERT INTO registration (event_id, u_id, attendance_status) VALUES (?, ?, ?)";
    $reg_stmt = $conn->prepare($reg_sql);
    $reg_stmt->bind_param("iis", $event_id, $u_id, $attendance_status);

    if ($reg_stmt->execute()) {
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Registered successfully!"]);
    } else {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Registration error: " . $conn->error]);
    }
    $reg_stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Transaction error: " . $e->getMessage()]);
}

$conn->close();

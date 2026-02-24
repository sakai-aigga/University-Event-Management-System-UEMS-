<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include '../../includes/db-config.php';

if (isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    
    // Attempt to fetch event. We'll handle the category name separately to be safe.
    $sql = "SELECT * FROM event WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Manual category mapping since we are not sure about the category table
        $cat_map = [1=>'Academic', 2=>'Workshop', 3=>'Sports', 4=>'Cultural'];
        $event['category_name'] = isset($cat_map[$event['category_id']]) ? $cat_map[$event['category_id']] : 'General';
        
        session_start();
        $is_registered = false;
        if (isset($_SESSION['u_id'])) {
            $u_id = $_SESSION['u_id'];
            $reg_check = "SELECT reg_id FROM registration WHERE event_id = ? AND u_id = ?";
            $reg_stmt = $conn->prepare($reg_check);
            $reg_stmt->bind_param("ii", $event_id, $u_id);
            $reg_stmt->execute();
            if ($reg_stmt->get_result()->num_rows > 0) {
                $is_registered = true;
            }
            $reg_stmt->close();
        }
        $event['is_registered'] = $is_registered;
        
        echo json_encode([
            "success" => true,
            "event" => $event
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Event not found"
        ]);
    }
} else {
    // Fetch all published events
    $sql = "SELECT * FROM event WHERE is_published = 1 ORDER BY event_date ASC";
    $result = $conn->query($sql);

    $events = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    echo json_encode([
        "success" => true,
        "events" => $events
    ]);
}
?>
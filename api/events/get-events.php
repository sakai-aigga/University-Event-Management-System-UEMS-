<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include '../../includes/db-config.php';

if (isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    
    // Attempt to fetch event. We'll handle the category name separately to be safe.
    // JOIN with departments table to get full details
    $sql = "SELECT e.*, d.dept_name, d.acronym as dept_acronym,
            (SELECT COUNT(*) FROM registration r WHERE r.event_id = e.event_id) as current_participants
            FROM event e 
            LEFT JOIN departments d ON e.dept_id = d.dept_id 
            WHERE e.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Manual category mapping since we are not sure about the category table
        $cat_map = [1=>'Academic', 2=>'Workshop', 3=>'Sports', 4=>'Cultural'];
        $event['category_name'] = isset($cat_map[$event['category_id']]) ? $cat_map[$event['category_id']] : 'General';

        // Handle image with curated category-based fallback
        if (!empty($event['event_image'])) {
            $img = $event['event_image'];
            if (strpos($img, 'data:image') !== 0 && strpos($img, 'http') !== 0) {
                $event['event_image'] = 'data:image/jpeg;base64,' . base64_encode($img);
            }
        } else {
            $placeholders = [
                1 => 'https://images.unsplash.com/photo-1523050853063-bd8012fbb2a0?q=80&w=1000&auto=format&fit=crop', // Academic
                2 => 'https://images.unsplash.com/photo-1540575861501-7c93b177ef96?q=80&w=1000&auto=format&fit=crop', // Workshop
                3 => 'https://images.unsplash.com/photo-1461896756186-009f97c72c9c?q=80&w=1000&auto=format&fit=crop', // Sports
                4 => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1000&auto=format&fit=crop', // Cultural
            ];
            $event['event_image'] = isset($placeholders[$event['category_id']]) ? $placeholders[$event['category_id']] : 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop';
        }
        
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
            if (!empty($row['event_image'])) {
                $img = $row['event_image'];
                if (strpos($img, 'data:image') !== 0 && strpos($img, 'http') !== 0) {
                    $row['event_image'] = 'data:image/jpeg;base64,' . base64_encode($img);
                }
            } else {
                $placeholders = [
                    1 => 'https://images.unsplash.com/photo-1523050853063-bd8012fbb2a0?q=80&w=1000&auto=format&fit=crop', // Academic
                    2 => 'https://images.unsplash.com/photo-1540575861501-7c93b177ef96?q=80&w=1000&auto=format&fit=crop', // Workshop
                    3 => 'https://images.unsplash.com/photo-1461896756186-009f97c72c9c?q=80&w=1000&auto=format&fit=crop', // Sports
                    4 => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1000&auto=format&fit=crop', // Cultural
                ];
                $row['event_image'] = isset($placeholders[$row['category_id']]) ? $placeholders[$row['category_id']] : 'https://images.unsplash.com/photo-1541339907198-e08756ebafe3?q=80&w=1000&auto=format&fit=crop';
            }
            $events[] = $row;
        }
    }

    echo json_encode([
        "success" => true,
        "events" => $events
    ]);
}
?>
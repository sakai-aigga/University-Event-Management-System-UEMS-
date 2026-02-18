<?php
include 'includes/db-config.php';

// First, check the constraint name
$sql = "SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'event' AND COLUMN_NAME = 'u_id' AND TABLE_SCHEMA = '$dbname'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $constraint = $row['CONSTRAINT_NAME'];
        echo "Found constraint: $constraint\n";
        
        // Drop the constraint
        $drop_sql = "ALTER TABLE event DROP FOREIGN KEY $constraint";
        if ($conn->query($drop_sql)) {
            echo "Dropped foreign key constraint: $constraint\n";
        } else {
            echo "Error dropping constraint: " . $conn->error . "\n";
        }
    }
} else {
    echo "No foreign key found on u_id.\n";
}

// Add new foreign key to users table
$add_sql = "ALTER TABLE event ADD CONSTRAINT fk_event_user FOREIGN KEY (u_id) REFERENCES users(u_id) ON DELETE CASCADE";
if ($conn->query($add_sql)) {
    echo "Added new foreign key constraint to users table.\n";
} else {
    echo "Error adding constraint: " . $conn->error . "\n";
}
?>

<?php
include 'includes/db-config.php';

$tables = ['organizer', 'users'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    $cols = $conn->query("DESCRIBE $table");
    if ($cols) {
        while($row = $cols->fetch_assoc()) {
            echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "  Error: " . $conn->error . "\n";
    }
    echo "\n";
}
?>

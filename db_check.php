<?php
require_once __DIR__ . '/db.php';

echo "Database connection successful!\n\n";

$res = mysqli_query($conn, 'SHOW TABLES');
while ($row = mysqli_fetch_row($res)) {
    echo "TABLE: " . $row[0] . "\n";
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM " . $row[0]);
    while ($col = mysqli_fetch_assoc($cols)) {
        echo " - " . $col['Field'] . "\n";
    }
}
?>
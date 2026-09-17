<?php
$conn = mysqli_connect("localhost", "root", "", "24shop");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent INT NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    expire_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

mysqli_query($conn, $sql);

// Insert dummy coupons
$dummy = [
    "('SAVE10', 10, '10% off on all items', '2026-07-01', '2026-12-31')",
    "('SAVE20', 20, '20% discount on purchases', '2026-07-15', '2026-08-15')",
    "('WELCOME', 15, 'Welcome offer for new customers', '2026-06-01', '2026-09-30')",
    "('SUMMER', 25, 'Summer sale - extra 25% off', '2026-07-01', '2026-08-31')",
    "('FESTIVE30', 30, 'Festive Special 30% Off', '2026-08-01', '2026-12-31')"
];

foreach ($dummy as $val) {
    mysqli_query($conn, "INSERT IGNORE INTO coupons (code, discount_percent, description, start_date, expire_date) VALUES $val");
}

echo "Done";
?>

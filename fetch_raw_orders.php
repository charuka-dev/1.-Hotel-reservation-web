<?php
// fetch_raw_orders.php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "foodordering");

// Check connection
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// SQL query to get all raw orders data
$sql = "SELECT id, userId, foodId, totalPrice, status, orderTime FROM orders ORDER BY orderTime DESC";
$result = $conn->query($sql);

$orders = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode($orders);
$conn->close();
?>

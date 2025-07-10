<?php
// fetch_raw_orders.php

include 'db_connection.php'; // your DB connection script

$sql = "SELECT id, userId, foodId, totalPrice, status, orderTime FROM orders ORDER BY orderTime DESC";

$result = $conn->query($sql);

$userId = $_GET['userId'] ?? null;

if (!$userId) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, userId, foodId, totalPrice, status, orderTime FROM orders WHERE userId = ? ORDER BY orderTime DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: application/json');
echo json_encode($orders);
?>

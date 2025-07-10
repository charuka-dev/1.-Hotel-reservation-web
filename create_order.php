<?php
// filepath: c:\xampp\htdocs\1.-Hotel-reservation-web\create_order.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$userId = intval($data['userId']);
$foodId = intval($data['foodId']);
$conn = new mysqli("localhost", "root", "", "foodordering");

// Get last id and increment by 1
$res = $conn->query("SELECT MAX(id) AS max_id FROM orders");
$row = $res->fetch_assoc();
$newId = ($row['max_id'] !== null) ? intval($row['max_id']) + 1 : 1;

$stmt = $conn->prepare("INSERT INTO orders (id, userId, foodId, orderTime) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iii", $newId, $userId, $foodId);

if($stmt->execute()) {
    echo json_encode(['success'=>true, 'orderId'=>$newId]);
} else {
    echo json_encode(['success'=>false, 'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
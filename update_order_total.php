<?php
// filepath: c:\xampp\htdocs\1.-Hotel-reservation-web\update_order_total.php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$orderId = intval($data['orderId']);
$totalPrice = floatval($data['totalPrice']);
$conn = new mysqli("localhost", "root", "", "foodordering");
$stmt = $conn->prepare("UPDATE orders SET totalPrice=? WHERE id=?");
$stmt->bind_param("di", $totalPrice, $orderId);
if($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
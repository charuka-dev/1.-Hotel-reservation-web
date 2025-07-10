<?php
// filepath: c:\xampp\htdocs\1.-Hotel-reservation-web\fetch_orders.php
header('Content-Type: application/json');
$userId = intval($_GET['userId']);
$conn = new mysqli("localhost", "root", "", "foodordering");
$sql = "SELECT o.id as orderId, f.imageUrl AS image, f.name, f.price 
        FROM orders o 
        JOIN fooditems f ON o.foodId = f.id 
        WHERE o.userId = $userId";
$res = $conn->query($sql);
$orders = [];
while($row = $res->fetch_assoc()) $orders[] = $row;
echo json_encode($orders);
$conn->close();
?>
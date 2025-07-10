<?php
// filepath: c:\xampp\htdocs\1.-Hotel-reservation-web\fetch_foods.php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "foodordering");
$res = $conn->query("SELECT id, imageUrl, name, category, price, status FROM fooditems");
$foods = [];
while($row = $res->fetch_assoc()) {
    $row['image'] = $row['imageUrl']; // Add this line
    $foods[] = $row;
}
echo json_encode($foods);
$conn->close();
?>
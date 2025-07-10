<?php
// fetch_user.php
header('Content-Type: application/json');

$userId = intval($_GET['userId']);

// Database connection
$conn = new mysqli("localhost", "root", "", "foodordering");

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// SQL query to get user data
$sql = "SELECT id, username, password, address, phone, email FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
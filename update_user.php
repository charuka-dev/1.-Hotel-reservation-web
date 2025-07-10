<?php
// update_user.php
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$userId = intval($input['userId']);
$username = $input['username'];
$password = $input['password'];
$address = $input['address'];
$phone = $input['phone'];
$email = $input['email'];

// Validate required fields
if (!$username || !$password || !$email) {
    echo json_encode(['success' => false, 'message' => 'Username, password, and email are required']);
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "foodordering");

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if username or email already exists for other users
$checkSql = "SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ssi", $username, $email, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    $checkStmt->close();
    $conn->close();
    exit;
}

// Update user data
$sql = "UPDATE user SET username = ?, password = ?, address = ?, phone = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $username, $password, $address, $phone, $email, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$checkStmt->close();
$conn->close();
?>
<?php
// delete_user.php
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$userId = intval($input['userId']);

// Validate user ID
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "foodordering");

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Start transaction
$conn->autocommit(false);

try {
    // First delete all orders related to this user
    $deleteOrdersSql = "DELETE FROM orders WHERE userId = ?";
    $deleteOrdersStmt = $conn->prepare($deleteOrdersSql);
    $deleteOrdersStmt->bind_param("i", $userId);
    $deleteOrdersStmt->execute();
    
    // Then delete the user
    $deleteUserSql = "DELETE FROM user WHERE id = ?";
    $deleteUserStmt = $conn->prepare($deleteUserSql);
    $deleteUserStmt->bind_param("i", $userId);
    
    if ($deleteUserStmt->execute()) {
        if ($deleteUserStmt->affected_rows > 0) {
            // Commit transaction
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
        } else {
            // Rollback transaction
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
    }
    
    $deleteOrdersStmt->close();
    $deleteUserStmt->close();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting account: ' . $e->getMessage()]);
}

$conn->autocommit(true);
$conn->close();
?>
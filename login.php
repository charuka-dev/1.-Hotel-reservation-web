<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? '');
    $id = trim($_POST['id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate input
    if (empty($role) || empty($id) || empty($password)) {
        header("Location: login.html?error=All fields are required");
        exit();
    }
    
    try {
        if ($role === 'customer') {
            // Check user table for customer login
            $stmt = $conn->prepare("SELECT id, userName, password FROM user WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session and redirect to userdashboard.html
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['userName'];
                $_SESSION['role'] = 'customer';
                header("Location: userdashboard.html");
                exit();
            } else {
                header("Location: login.html?error=Invalid ID or password");
                exit();
            }
            
        } else if ($role === 'admin') {
            // Check admin table for admin login
            $stmt = $conn->prepare("SELECT id, userName, password FROM admin WHERE id = ?");
            $stmt->execute([$id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful - set session and redirect to index.html
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['userName'];
                $_SESSION['role'] = 'admin';
                header("Location: index.html");
                exit();
            } else {
                header("Location: login.html?error=Invalid ID or password");
                exit();
            }
            
        } else {
            header("Location: login.html?error=Invalid role selected");
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.html?error=Database error occurred");
        exit();
    }
    
} else {
    // If not POST request, redirect to login page
    header("Location: login.html");
    exit();
}
?>
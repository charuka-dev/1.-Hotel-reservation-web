<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate input
    if (empty($role) || empty($username) || empty($password)) {
        header("Location: login.html?error=All fields are required");
        exit();
    }
    
    try {
        if ($role === 'Customer') {
            // Check user table for customer login
            $stmt = $conn->prepare("SELECT id, userName, password FROM user WHERE userName = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session and redirect to index.html
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'customer';
                header("Location: index.html");
                exit();
            } else {
                header("Location: login.html?error=Invalid username or password");
                exit();
            }
            
        } else if ($role === 'Admin') {
            // Check admin table for admin login
            $stmt = $conn->prepare("SELECT id, userName, password FROM admin WHERE userName = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful - set session and redirect to userdashboard.html
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['name'];
                $_SESSION['role'] = 'Admin';
                header("Location: userdashboard.html");
                exit();
            } else {
                header("Location: login.html?error=Invalid username or password");
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
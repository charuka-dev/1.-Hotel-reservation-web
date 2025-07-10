<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Change if you have a password
$dbname = "foodordering";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data and sanitize
$id = trim($_POST['id']);
$user = trim($_POST['username']);
$pass = trim($_POST['password']);
$address = trim($_POST['address']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);

// Simple validation (server-side)
if (
    empty($id) || empty($user) || empty($pass) ||
    empty($address) || empty($phone) || empty($email)
) {
    showMessage(false, "All fields are required.");
    exit;
}

// Hash the password for security
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO user (id, username, password, address, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $id, $user, $hashed_pass, $address, $phone, $email);

if ($stmt->execute()) {
    showMessage(true, "Registration successful!");
} else {
    showMessage(false, "Error: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Function to show message and button
function showMessage($success, $msg) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Registration Status</title>
        <style>
            body {
                background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                font-family: Arial, sans-serif;
            }
            .message-box {
                background: rgba(255,255,255,0.95);
                padding: 32px 40px;
                border-radius: 16px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                text-align: center;
            }
            .message-box h2 {
                color: <?php echo $success ? "#28a745" : "#dc3545"; ?>;
                margin-bottom: 18px;
            }
            .message-box button {
                padding: 10px 24px;
                border: none;
                border-radius: 8px;
                background: <?php echo $success ? "#007bff" : "#dc3545"; ?>;
                color: #fff;
                font-size: 16px;
                cursor: pointer;
                margin-top: 18px;
            }
            .message-box button:hover {
                background: <?php echo $success ? "#0056b3" : "#c82333"; ?>;
            }
        </style>
    </head>
    <body>
        <div class="message-box">
            <h2><?php echo $msg; ?></h2>
            <?php if ($success): ?>
                <button onclick="window.location.href='login.html'">Login</button>
            <?php else: ?>
                <button onclick="document.querySelector('.message-box').style.display='none'">Try Again</button>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>
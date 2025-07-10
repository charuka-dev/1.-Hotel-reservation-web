<?php
session_start();
require_once 'db.php';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteUserId = (int) $_POST['delete_user_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteUserId]);

        $_SESSION['message'] = "User deleted successfully.";
    } catch (PDOException $e) {
        // Foreign key constraint violation error code is 23000 in PDO
        if ($e->getCode() == 23000) {
            $_SESSION['message'] = "Cannot delete user because there are existing orders linked to this user. Please delete or reassign those orders first.";
        } else {
            $_SESSION['message'] = "An error occurred while deleting the user.";
        }
    }

    header("Location: manage_users.php");
    exit();
}

// Fetch users (including address)
$stmt = $conn->query("SELECT id, username, email, phone, address FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f4f8;
      color: #0d6efd;
      padding: 30px;
    }
    h1 {
      margin-bottom: 20px;
    }
    table {
      background: white;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(13, 110, 253, 0.15);
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background: #0d6efd;
      color: white;
      border-radius: 10px 10px 0 0;
    }
    tr {
      background: #fff;
      box-shadow: 0 5px 10px rgba(13, 110, 253, 0.05);
    }
    tr td:last-child {
      width: 120px;
    }
    button.delete-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button.delete-btn:hover {
      background-color: #b02a37;
    }
    .message {
      margin-bottom: 20px;
      padding: 10px;
      border-radius: 8px;
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      max-width: 400px;
    }
    a.back-link {
      display: inline-block;
      margin-top: 20px;
      color: #0d6efd;
      text-decoration: none;
      font-weight: 600;
    }
    a.back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <h1>Manage Users</h1>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="message"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
  <?php endif; ?>

  <?php if (count($users) === 0): ?>
    <p>No users found.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?php echo htmlspecialchars($user['id']); ?></td>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td><?php echo htmlspecialchars($user['phone']); ?></td>
        <td><?php echo htmlspecialchars($user['address']); ?></td>
        <td>
          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
            <input type="hidden" name="delete_user_id" value="<?php echo (int)$user['id']; ?>" />
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <a href="index.html" class="back-link">&larr; Back to Dashboard</a>
</body>
</html>

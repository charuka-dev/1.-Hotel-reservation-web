<?php
session_start();
require_once 'db.php';

$editing = false;
$error = '';
$user = ['id'=>'', 'name'=>'', 'password'=>'', 'address'=>'', 'phone'=>'', 'email'=>''];

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$deleteId]);
        $_SESSION['message'] = "User ID $deleteId deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error deleting user: " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && isset($_POST['editing'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$id || !$name || !$password || !$address || !$phone || !$email) {
        $error = "All fields are required.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE admin SET name=?, password=?, address=?, phone=?, email=? WHERE id=?");
            $stmt->execute([$name, $password, $address, $phone, $email, $id]);
            $_SESSION['message'] = "User ID $id updated successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}

// Load user data if editing
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->execute([$editId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $editing = true;
    } else {
        $_SESSION['message'] = "User not found.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all users
$stmt = $conn->query("SELECT * FROM admin ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage User Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    background-color: #f8f9fa;
    padding: 30px;
  }
  .form-container {
    max-width: 600px;
    margin-bottom: 40px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgb(0 0 0 / 0.1);
  }
  table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgb(0 0 0 / 0.1);
  }
  .message, .error {
    max-width: 600px;
  }
</style>
</head>
<body>

<div class="container">
  <h1 class="mb-4 text-primary">Manage User Profile</h1>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-success message"><?= htmlspecialchars($_SESSION['message']) ?></div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($editing): ?>
    <div class="form-container">
      <h4>Edit User ID <?= htmlspecialchars($user['id']) ?></h4>
      <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>" />
        <input type="hidden" name="editing" value="1" />

        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" value="<?= htmlspecialchars($user['password']) ?>" required />
        </div>

        <div class="mb-3">
          <label for="address" class="form-label">Address</label>
          <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>" required />
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Phone</label>
          <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required />
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required />
        </div>

        <button type="submit" name="save" class="btn btn-primary">Update User</button>
        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary ms-2">Cancel</a>
      </form>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($users) === 0): ?>
          <tr><td colspan="6" class="text-center">No users found.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['id']) ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['phone']) ?></td>
              <td><?= htmlspecialchars($u['address']) ?></td>
              <td>
                <a href="?edit=<?= (int)$u['id'] ?>" class="btn btn-sm btn-primary">Edit</a>

                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user ID <?= (int)$u['id'] ?>?');">
                  <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>" />
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="index.html" class="btn btn-link mt-3">&larr; Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

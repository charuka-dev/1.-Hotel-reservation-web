<?php
session_start();
require_once 'db.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);

    $_SESSION['message'] = "Order #$orderId status updated successfully.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all orders with JOINs
$stmt = $conn->query("
    SELECT o.id, u.username, f.name AS food_name, o.totalPrice, o.status, o.orderTime
    FROM orders o
    LEFT JOIN users u ON o.userId = u.id
    LEFT JOIN foodItems f ON o.foodId = f.id
    ORDER BY o.orderTime DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Orders</title>
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
    .message {
      margin-bottom: 20px;
      padding: 10px;
      border-radius: 8px;
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      max-width: 500px;
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
    .status-form {
      display: flex;
      gap: 8px;
    }
    .back-link {
      margin-top: 20px;
      display: inline-block;
      color: #0d6efd;
      text-decoration: none;
      font-weight: 600;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <h1>Customer Orders</h1>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
  <?php endif; ?>

  <?php if (count($orders) === 0): ?>
    <p>No orders found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Username</th>
          <th>Food Item</th>
          <th>Total Price (Rs.)</th>
          <th>Status</th>
          <th>Ordered At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td><?= htmlspecialchars($order['id']) ?></td>
          <td><?= htmlspecialchars($order['username'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($order['food_name'] ?? 'N/A') ?></td>
          <td><?= number_format($order['totalPrice'], 2) ?></td>
          <td>
            <form method="POST" class="status-form">
              <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>" />
              <select name="status" class="form-select form-select-sm" required>
                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
              <button type="submit" name="update_status" class="btn btn-sm btn-success">Update</button>
            </form>
          </td>
          <td><?= htmlspecialchars($order['orderTime']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="index.html" class="back-link">&larr; Back to Dashboard</a>

</body>
</html>

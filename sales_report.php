<?php
session_start();
require_once 'db.php';

// Get overall stats
$totalStmt = $conn->query("SELECT COUNT(*) AS total_orders, SUM(total) AS total_income FROM sales");
$totals = $totalStmt->fetch(PDO::FETCH_ASSOC);

// Get most sold food item
$topFoodStmt = $conn->query("
    SELECT f.name, SUM(s.quantity) AS total_quantity
    FROM sales s
    JOIN foodItems f ON s.foodItemId = f.id
    GROUP BY s.foodItemId
    ORDER BY total_quantity DESC
    LIMIT 1
");
$topFood = $topFoodStmt->fetch(PDO::FETCH_ASSOC);

// Get top user
$topUserStmt = $conn->query("
    SELECT u.username, COUNT(*) AS total_orders
    FROM sales s
    JOIN users u ON s.userId = u.id
    GROUP BY s.userId
    ORDER BY total_orders DESC
    LIMIT 1
");
$topUser = $topUserStmt->fetch(PDO::FETCH_ASSOC);

// Get recent sales
$recentStmt = $conn->query("
    SELECT s.*, u.username, f.name AS food_name
    FROM sales s
    JOIN users u ON s.userId = u.id
    JOIN foodItems f ON s.foodItemId = f.id
    ORDER BY s.order_date DESC
    LIMIT 10
");
$recentSales = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f8f9fa;
      padding: 30px;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      margin-bottom: 20px;
      box-shadow: 0 8px 20px rgba(13, 110, 253, 0.1);
    }
    h1 {
      margin-bottom: 30px;
      color: #0d6efd;
    }
    .back-link {
      margin-top: 30px;
      display: inline-block;
    }
  </style>
</head>
<body>
  <h1>Sales Report</h1>

  <div class="row">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Total Orders</h5>
        <p class="fs-4"><?= htmlspecialchars($totals['total_orders'] ?? 0) ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Total Income (Rs.)</h5>
        <p class="fs-4">Rs. <?= number_format($totals['total_income'] ?? 0, 2) ?></p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Most Sold Item</h5>
        <p class="fs-5"><?= htmlspecialchars($topFood['name'] ?? 'N/A') ?> (<?= $topFood['total_quantity'] ?? 0 ?> sold)</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Top Customer</h5>
        <p class="fs-5"><?= htmlspecialchars($topUser['username'] ?? 'N/A') ?> (<?= $topUser['total_orders'] ?? 0 ?> orders)</p>
      </div>
    </div>
  </div>

  <h4 class="mt-4">Recent Sales</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Food Item</th>
        <th>Quantity</th>
        <th>Total (Rs.)</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentSales as $sale): ?>
      <tr>
        <td><?= htmlspecialchars($sale['id']) ?></td>
        <td><?= htmlspecialchars($sale['username']) ?></td>
        <td><?= htmlspecialchars($sale['food_name']) ?></td>
        <td><?= htmlspecialchars($sale['quantity']) ?></td>
        <td><?= number_format($sale['total'], 2) ?></td>
        <td><?= htmlspecialchars($sale['order_date']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="index.html" class="btn btn-outline-primary back-link">&larr; Back to Dashboard</a>
</body>
</html>

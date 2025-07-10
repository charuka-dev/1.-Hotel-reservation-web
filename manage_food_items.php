<?php
session_start();
require_once 'db.php';

// Initialize variables
$editing = false;
$item = [
    'id' => '',
    'name' => '',
    'category' => '',
    'price' => '',
    'imageUrl' => '',
    'status' => '',
];
$error = '';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM foodItems WHERE id = ?");
    $stmt->execute([$deleteId]);
    $_SESSION['message'] = "Food item deleted successfully.";
    header("Location: manage_food_items.php");
    exit();
}

// Handle Add or Update form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['imageUrl'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if (!$id || !$name || !$category || $price <= 0 || !$status) {
        $error = "Please fill in all required fields correctly.";
        $item = compact('id', 'name', 'category', 'price', 'imageUrl', 'status');
        $editing = $id > 0;
    } else {
        if ($id > 0 && isset($_POST['editing'])) {
            // Update existing
            $stmt = $conn->prepare("UPDATE foodItems SET name = ?, category = ?, price = ?, imageUrl = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $category, $price, $imageUrl, $status, $id]);
            $_SESSION['message'] = "Food item updated successfully.";
            header("Location: manage_food_items.php");
            exit();
        } else {
            // Insert new — check for duplicate id
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM foodItems WHERE id = ?");
            $checkStmt->execute([$id]);
            if ($checkStmt->fetchColumn() > 0) {
                $error = "This ID already exists. Please choose another one.";
                $item = compact('id', 'name', 'category', 'price', 'imageUrl', 'status');
            } else {
                $stmt = $conn->prepare("INSERT INTO foodItems (id, name, category, price, imageUrl, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $name, $category, $price, $imageUrl, $status]);
                $_SESSION['message'] = "Food item added successfully.";
                header("Location: manage_food_items.php");
                exit();
            }
        }
    }
}

// If editing, load data for that id (from GET param)
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $stmt = $conn->prepare("SELECT * FROM foodItems WHERE id = ?");
        $stmt->execute([$editId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $editing = true;
        } else {
            $_SESSION['message'] = "Food item not found.";
            header("Location: manage_food_items.php");
            exit();
        }
    }
}

// Fetch all items for listing
$stmt = $conn->query("SELECT * FROM foodItems ORDER BY id DESC");
$foodItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Food Items</title>
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
    margin-bottom: 40px;
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
    width: 160px;
  }
  img.food-image {
    max-width: 80px;
    max-height: 60px;
    object-fit: cover;
    border-radius: 6px;
  }
  button.delete-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-left: 8px;
  }
  button.delete-btn:hover {
    background-color: #b02a37;
  }
  a.edit-btn {
    background-color: #0d6efd;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }
  a.edit-btn:hover {
    background-color: #084298;
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
  .error {
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 8px;
    background-color: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
    max-width: 400px;
  }
  form.food-form {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.15);
    max-width: 500px;
  }
  form.food-form label {
    font-weight: 600;
  }
  form.food-form input,
  form.food-form select {
    margin-bottom: 15px;
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
  }
  form.food-form button {
    padding: 10px 15px;
    font-weight: 600;
  }
  .back-link {
    display: inline-block;
    margin-top: 20px;
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

<h1>Manage Food Items</h1>

<?php if (!empty($_SESSION['message'])): ?>
  <div class="message"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Food item form: Add or Edit -->
<form method="POST" class="food-form" novalidate>
  <label for="id">ID *</label>
  <input type="number" id="id" name="id" required min="1" value="<?= htmlspecialchars($item['id']) ?>" <?= $editing ? 'readonly' : '' ?> />

  <label for="name">Name *</label>
  <input type="text" id="name" name="name" required value="<?= htmlspecialchars($item['name']) ?>" />

  <label for="category">Category *</label>
  <input type="text" id="category" name="category" required value="<?= htmlspecialchars($item['category']) ?>" />

  <label for="price">Price (Rs.) *</label>
  <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($item['price']) ?>" />

  <label for="imageUrl">Image URL</label>
  <input type="url" id="imageUrl" name="imageUrl" value="<?= htmlspecialchars($item['imageUrl']) ?>" />

  <label for="status">Status *</label>
  <select id="status" name="status" required>
    <option value="" <?= $item['status'] === '' ? 'selected' : '' ?>>Select status</option>
    <option value="available" <?= $item['status'] === 'available' ? 'selected' : '' ?>>Available</option>
    <option value="unavailable" <?= $item['status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
  </select>

  <button type="submit" name="save" class="btn btn-primary">
    <?= $editing ? 'Update Food Item' : 'Add Food Item' ?>
  </button>
  <?php if ($editing): ?>
    <a href="manage_food_items.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <input type="hidden" name="editing" value="1" />
  <?php endif; ?>
</form>

<!-- Food items list -->
<?php if (count($foodItems) === 0): ?>
  <p>No food items found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Category</th><th>Price (Rs.)</th><th>Status</th><th>Image</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($foodItems as $food): ?>
      <tr>
        <td><?= htmlspecialchars($food['id']) ?></td>
        <td><?= htmlspecialchars($food['name']) ?></td>
        <td><?= htmlspecialchars($food['category']) ?></td>
        <td><?= number_format($food['price'], 2) ?></td>
        <td><?= htmlspecialchars(ucfirst($food['status'])) ?></td>
        <td>
          <?php if ($food['imageUrl']): ?>
            <img src="<?= htmlspecialchars($food['imageUrl']) ?>" alt="Image" class="food-image" />
          <?php else: ?>
            No image
          <?php endif; ?>
        </td>
        <td>
          <a href="?edit=<?= (int)$food['id'] ?>" class="edit-btn">Edit</a>
          <form method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this food item?');">
            <input type="hidden" name="delete_id" value="<?= (int)$food['id'] ?>" />
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

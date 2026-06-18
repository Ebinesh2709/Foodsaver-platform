<?php
session_start();
require_once '../config/db.php'; // adjust path if your folder structure differs

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php");
    exit;
}
$business_id = $_SESSION['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_name       = trim($_POST['food_name'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $quantity        = trim($_POST['quantity'] ?? '');
    $expiry_datetime = trim($_POST['expiry_datetime'] ?? '');

    if ($food_name === '') $errors[] = "Food name is required.";
    if ($quantity === '' || !is_numeric($quantity) || $quantity <= 0) $errors[] = "Quantity must be a positive number.";
    if ($expiry_datetime === '') $errors[] = "Expiry date/time is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO food_listings (business_id, food_name, description, quantity, expiry_datetime, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'available', NOW())"
        );
        $stmt->bind_param("issss", $business_id, $food_name, $description, $quantity, $expiry_datetime);

        if ($stmt->execute()) {
            header("Location: my_listings.php?success=1");
            exit;
        }
        $errors[] = "Could not save the listing. Try again.";
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Add Listing</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Post a Food Listing</h2>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="POST">
  <div class="mb-3"><label class="form-label">Food Name</label>
    <input type="text" name="food_name" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3"></textarea></div>
  <div class="mb-3"><label class="form-label">Quantity</label>
    <input type="number" step="0.1" name="quantity" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Expiry Date & Time</label>
    <input type="datetime-local" name="expiry_datetime" class="form-control" required></div>
  <button class="btn btn-primary">Post Listing</button>
  <a href="my_listings.php" class="btn btn-secondary">Cancel</a>
</form>
</div></body></html>
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$business_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, food_name, quantity, expiry_datetime, status FROM food_listings WHERE business_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $business_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>My Listings</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>My Food Listings</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Saved successfully.</div><?php endif; ?>
<a href="add_listing.php" class="btn btn-primary mb-3">+ Add Listing</a>
<table class="table table-bordered">
<thead><tr><th>Food</th><th>Qty</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['food_name']) ?></td>
  <td><?= htmlspecialchars($row['quantity']) ?></td>
  <td><?= htmlspecialchars($row['expiry_datetime']) ?></td>
  <td><?= htmlspecialchars($row['status']) ?></td>
  <td>
    <a href="edit_listing.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="delete_listing.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody></table>
</div></body></html>
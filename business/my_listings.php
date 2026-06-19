<?php
session_start();
define('APP_RUNNING', true);
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$stmt_biz = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt_biz->execute([$_SESSION['user_id']]);
$business = $stmt_biz->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    die("No business profile found. Please complete your business registration first.");
}
$business_id = $business['id'];

$stmt = $pdo->prepare("SELECT id, title, quantity, pickup_end, status, urgency_score FROM food_listings WHERE business_id = ? ORDER BY created_at DESC");
$stmt->execute([$business_id]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>My Listings</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>My Food Listings</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Listing saved successfully.</div><?php endif; ?>
<a href="add_listing.php" class="btn btn-primary mb-3">+ Add Listing</a>
<table class="table table-bordered">
<thead><tr><th>Title</th><th>Qty</th><th>Pickup Ends</th><th>Urgency</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($listings as $row): ?>
<tr>
  <td><?= htmlspecialchars($row['title']) ?></td>
  <td><?= htmlspecialchars($row['quantity']) ?></td>
  <td><?= htmlspecialchars($row['pickup_end']) ?></td>
  <td><span class="badge bg-<?= $row['urgency_score']==='high' ? 'danger' : ($row['urgency_score']==='medium' ? 'warning' : 'secondary') ?>">
    <?= htmlspecialchars($row['urgency_score'] ?? 'low') ?></span></td>
  <td><?= htmlspecialchars($row['status']) ?></td>
  <td>
    <a href="edit_listing.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="delete_listing.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this listing?')">Delete</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></body></html>
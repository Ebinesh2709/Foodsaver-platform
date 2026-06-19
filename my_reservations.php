<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT r.id, fl.food_name, fl.quantity, r.status, r.created_at
    FROM reservations r JOIN food_listings fl ON r.listing_id = fl.id
    WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>My Reservations</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>My Reservations</h2>
<table class="table table-bordered">
<thead><tr><th>Food</th><th>Qty</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($reservations as $row): ?>
<tr>
  <td><?= htmlspecialchars($row['food_name']) ?></td>
  <td><?= htmlspecialchars($row['quantity']) ?></td>
  <td><?= htmlspecialchars($row['status']) ?></td>
  <td><?= htmlspecialchars($row['created_at']) ?></td>
  <td><?php if ($row['status'] === 'pending'): ?>
    <a href="cancel_reservation.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel?')">Cancel</a>
  <?php endif; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></body></html>
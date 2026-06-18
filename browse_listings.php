<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$sql = "SELECT fl.id, fl.food_name, fl.description, fl.quantity, fl.expiry_datetime, u.name AS business_name
        FROM food_listings fl JOIN users u ON fl.business_id = u.id
        WHERE fl.status = 'available' ORDER BY fl.expiry_datetime ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Browse Food</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Available Food</h2><div class="row">
<?php while ($row = $result->fetch_assoc()): ?>
<div class="col-md-4 mb-3"><div class="card"><div class="card-body">
  <h5><?= htmlspecialchars($row['food_name']) ?></h5>
  <p><?= htmlspecialchars($row['description']) ?></p>
  <p><strong>Qty:</strong> <?= htmlspecialchars($row['quantity']) ?></p>
  <p><strong>Expires:</strong> <?= htmlspecialchars($row['expiry_datetime']) ?></p>
  <p><strong>From:</strong> <?= htmlspecialchars($row['business_name']) ?></p>
  <form method="POST" action="reserve_listing.php">
    <input type="hidden" name="listing_id" value="<?= $row['id'] ?>">
    <button class="btn btn-success">Reserve</button>
  </form>
</div></div></div>
<?php endwhile; ?>
</div></div></body></html>
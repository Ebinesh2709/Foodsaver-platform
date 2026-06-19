<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? null;
    $new_status = $_POST['new_status'] ?? null;
    if ($reservation_id && in_array($new_status, ['confirmed', 'collected'])) {
        $stmt = $pdo->prepare("UPDATE reservations r JOIN food_listings fl ON r.listing_id = fl.id
            SET r.status = ? WHERE r.id = ? AND fl.business_id = ?");
        $stmt->execute([$new_status, $reservation_id, $business_id]);

        if ($new_status === 'collected') {
            $u = $pdo->prepare("UPDATE food_listings fl JOIN reservations r ON r.listing_id = fl.id
                SET fl.status = 'collected' WHERE r.id = ? AND fl.business_id = ?");
            $u->execute([$reservation_id, $business_id]);
        }
    }
}

$stmt = $pdo->prepare("SELECT r.id, fl.food_name, r.status, u.name AS user_name
    FROM reservations r JOIN food_listings fl ON r.listing_id = fl.id JOIN users u ON r.user_id = u.id
    WHERE fl.business_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$business_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Manage Reservations</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Reservations for My Listings</h2>
<table class="table table-bordered">
<thead><tr><th>Food</th><th>Reserved By</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($reservations as $row): ?>
<tr>
  <td><?= htmlspecialchars($row['food_name']) ?></td>
  <td><?= htmlspecialchars($row['user_name']) ?></td>
  <td><?= htmlspecialchars($row['status']) ?></td>
  <td>
  <?php if ($row['status'] === 'pending'): ?>
    <form method="POST" style="display:inline">
      <input type="hidden" name="reservation_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="new_status" value="confirmed">
      <button class="btn btn-sm btn-primary">Confirm</button>
    </form>
  <?php elseif ($row['status'] === 'confirmed'): ?>
    <form method="POST" style="display:inline">
      <input type="hidden" name="reservation_id" value="<?= $row['id'] ?>">
      <input type="hidden" name="new_status" value="collected">
      <button class="btn btn-sm btn-success">Mark Collected</button>
    </form>
  <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></body></html>
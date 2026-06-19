<?php
session_start();
define('APP_RUNNING', true);
require_once 'includes/csrf_helper.php';
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT r.id, fl.title, fl.quantity, r.status, r.created_at
    FROM reservations r JOIN food_listings fl ON r.listing_id = fl.id
    WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>My Reservations</h2>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">You have no reservations yet.
            <a href="browse_listings.php">Browse available food</a>
        </div>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Food</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Reserved On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td>
                    <span class="badge bg-<?= 
                        $row['status'] === 'confirmed' ? 'success' : 
                        ($row['status'] === 'cancelled' ? 'danger' : 
                        ($row['status'] === 'collected' ? 'secondary' : 'warning')) ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" action="cancel_reservation.php" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Cancel this reservation?')">
                                Cancel
                            </button>
                        </form>
                    <?php elseif ($row['status'] === 'confirmed'): ?>
                        <span class="text-success">Ready for pickup</span>
                    <?php elseif ($row['status'] === 'collected'): ?>
                        <span class="text-muted">Collected</span>
                    <?php elseif ($row['status'] === 'cancelled'): ?>
                        <span class="text-danger">Cancelled</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="browse_listings.php" class="btn btn-primary mt-2">Browse More Food</a>
</div>
</body>
</html>
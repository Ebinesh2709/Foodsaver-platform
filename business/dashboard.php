<?php
require '../includes/auth_check.php';
require_role('business');
require '../config/db.php';

$stmt = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$business = $stmt->fetch();
$business_id = $business['id'];

$listingsStmt = $pdo->prepare("SELECT * FROM food_listings WHERE business_id = ? ORDER BY created_at DESC");
$listingsStmt->execute([$business_id]);
$listings = $listingsStmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h2>
    <a href="add_listing.php" class="btn btn-success">+ Add New Listing</a>
</div>

<?php if (count($listings) === 0): ?>
    <p>You haven't posted any listings yet.</p>
<?php else: ?>
<div class="row">
    <?php foreach ($listings as $item): ?>
    <div class="col-md-4 mb-3">
        <div class="card">
            <?php if ($item['image']): ?>
                <img src="../assets/uploads/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" style="height:180px; object-fit:cover;">
            <?php endif; ?>
            <div class="card-body">
                <h5><?= htmlspecialchars($item['title']) ?></h5>
                <p>Qty: <?= $item['quantity'] ?> | Rs.<?= $item['discounted_price'] ?></p>
                <span class="badge bg-secondary"><?= $item['status'] ?></span>
                <div class="mt-2">
                    <a href="edit_listing.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="delete_listing.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this listing?')">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
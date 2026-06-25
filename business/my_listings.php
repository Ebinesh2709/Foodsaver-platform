<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header('Location: ../auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';

// Get business_id
$stmt = $pdo->prepare('SELECT id FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$biz_row = $stmt->fetch();

if (!$biz_row) {
    header('Location: dashboard.php');
    exit;
}
$business_id = $biz_row['id'];

// Fetch all listings for this business
$stmt2 = $pdo->prepare('SELECT * FROM food_listings WHERE business_id = ? ORDER BY created_at DESC');
$stmt2->execute([$business_id]);
$listings = $stmt2->fetchAll();

$page_title  = 'My Listings';
$active_page = 'my_listings';
$css_prefix  = '../';
require_once '../includes/header.php';
?>

<div class="fs-page-header">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1><i class="bi bi-list-ul me-2"></i>My Listings</h1>
            <p>Manage your active food listings</p>
        </div>
        <a id="btn-add-listing-top" href="add_listing.php" class="btn btn-fs-white px-4">
            <i class="bi bi-plus-circle me-2"></i>Add New Listing
        </a>
    </div>
</div>

<div class="container pb-5">

    <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="fs-alert-success mb-3 alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>Listing added successfully — AI urgency score and summary assigned!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
        <div class="fs-alert-success mb-3">
            <i class="bi bi-check-circle me-2"></i>Listing updated successfully!
        </div>
    <?php endif; ?>

    <?php if (empty($listings)): ?>
        <div class="fs-empty">
            <span class="empty-icon">📋</span>
            <h2>No listings yet</h2>
            <p>Add your first surplus food listing to get started and help reduce food waste.</p>
            <a href="add_listing.php" class="btn btn-fs-primary px-4">Add First Listing</a>
        </div>
    <?php else: ?>
        <div class="table-responsive fs-table">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Qty</th>
                        <th>Price (LKR)</th>
                        <th>Pickup End</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars(ucfirst($listing['category']), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= get_urgency_badge_html($listing['urgency_score']) ?></td>
                        <td>
                            <?php
                            $status_map = [
                                'available' => 'bg-success',
                                'reserved'  => 'bg-primary',
                                'collected' => 'bg-secondary',
                                'sold_out'  => 'bg-dark',
                                'expired'   => 'bg-danger',
                            ];
                            $status_class = $status_map[$listing['status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $status_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $listing['status'])), ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td><?= htmlspecialchars($listing['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="fw-bold text-success"><?= htmlspecialchars(number_format((float)$listing['discounted_price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-nowrap small"><?= htmlspecialchars(date('d M Y, H:i', strtotime($listing['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-nowrap">
                            <a href="edit_listing.php?id=<?= (int)$listing['id'] ?>" class="btn btn-sm btn-fs-outline me-1" style="padding:0.28rem 0.7rem; font-size:0.78rem;">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="post" action="delete_listing.php" class="d-inline"
                                  onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-fs-danger" style="padding:0.28rem 0.7rem; font-size:0.78rem;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

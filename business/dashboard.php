<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header('Location: ../auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';

// Fetch business profile
$stmt = $pdo->prepare('SELECT * FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$business = $stmt->fetch();

if (!$business) {
    header('Location: ../auth/login.php');
    exit;
}

$business_id = $business['id'];

// Count active listings
$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM food_listings WHERE business_id = ? AND status = ?');
$stmt2->execute([$business_id, 'available']);
$active_listings = (int)$stmt2->fetchColumn();

// Count pending reservations
$stmt3 = $pdo->prepare(
    'SELECT COUNT(*) FROM reservations r
     JOIN food_listings fl ON r.listing_id = fl.id
     WHERE fl.business_id = ? AND r.status = ?'
);
$stmt3->execute([$business_id, 'pending']);
$pending_reservations = (int)$stmt3->fetchColumn();

$page_title  = 'Business Dashboard';
$active_page = 'dashboard';
$css_prefix  = '../';
require_once '../includes/header.php';
?>

<!-- Page Header -->
<div class="fs-page-header mx-auto mt-4 mb-4" style="max-width: 1200px; padding: 2rem 3rem;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1"><i class="bi bi-shop me-2" style="color:var(--fs-green-dark);"></i>Business Dashboard</h1>
            <p class="mb-0 text-muted">Welcome back, <?= htmlspecialchars($business['business_name'], ENT_QUOTES, 'UTF-8') ?> 👋</p>
        </div>
        <div>
            <a href="add_listing.php" class="btn btn-fs-primary shadow-sm"><i class="bi bi-plus-lg me-2"></i>New Listing</a>
        </div>
    </div>
</div>

<div class="container p-4 p-md-5 mb-5 mx-auto" style="max-width: 1200px;">

    <!-- Stat Cards -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6">
            <div class="stat-card solid-card d-flex align-items-center p-4 gap-4">
                <div class="stat-icon green" style="width: 80px; height: 80px; font-size: 2.5rem; border-radius: 20px;">📋</div>
                <div>
                    <div class="stat-value" style="font-size: 2.5rem; line-height: 1; color: var(--fs-text);"><?= $active_listings ?></div>
                    <div class="stat-label mt-1" style="font-size: 1rem;">Active Listings</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="stat-card solid-card d-flex align-items-center p-4 gap-4">
                <div class="stat-icon amber" style="width: 80px; height: 80px; font-size: 2.5rem; border-radius: 20px;">🔔</div>
                <div>
                    <div class="stat-value" style="font-size: 2.5rem; line-height: 1; color: var(--fs-text);"><?= $pending_reservations ?></div>
                    <div class="stat-label mt-1" style="font-size: 1rem;">Pending Reservations</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="h6 fw-bold mb-3" style="font-size:0.85rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--fs-text-muted);">QUICK ACTIONS</h2>
    <div class="row g-3">
        <div class="col-sm-4">
            <a id="btn-add-listing" href="add_listing.php" class="quick-action solid-card h-100 p-4 text-decoration-none d-block">
                <div class="qa-icon mb-3" style="font-size: 2rem;">➕</div>
                <div>
                    <div style="font-weight:600; font-size: 1.1rem; color: var(--fs-text);">Add New Listing</div>
                    <div style="font-size:0.85rem; color:var(--fs-text-muted);">Post surplus food quickly</div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a id="btn-my-listings" href="my_listings.php" class="quick-action solid-card h-100 p-4 text-decoration-none d-block">
                <div class="qa-icon mb-3" style="font-size: 2rem;">📋</div>
                <div>
                    <div style="font-weight:600; font-size: 1.1rem; color: var(--fs-text);">My Listings</div>
                    <div style="font-size:0.85rem; color:var(--fs-text-muted);">View &amp; manage your posts</div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a id="btn-manage-reservations" href="manage_reservations.php" class="quick-action solid-card h-100 p-4 text-decoration-none d-block">
                <div class="qa-icon mb-3" style="font-size: 2rem;">📅</div>
                <div>
                    <div style="font-weight:600; font-size: 1.1rem; color: var(--fs-text);">Reservations</div>
                    <div style="font-size:0.85rem; color:var(--fs-text-muted);">Confirm customer pickups</div>
                </div>
            </a>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

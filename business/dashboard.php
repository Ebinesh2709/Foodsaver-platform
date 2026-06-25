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
<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-shop me-2"></i>Business Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($business['business_name'], ENT_QUOTES, 'UTF-8') ?> 👋</p>
    </div>
</div>

<div class="container pb-5">

    <!-- Stat Cards -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6">
            <div class="stat-card">
                <div class="stat-icon green">📋</div>
                <div>
                    <div class="stat-value"><?= $active_listings ?></div>
                    <div class="stat-label">Active Listings</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="stat-card">
                <div class="stat-icon amber">🔔</div>
                <div>
                    <div class="stat-value"><?= $pending_reservations ?></div>
                    <div class="stat-label">Pending Reservations</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="h6 fw-bold mb-3" style="font-size:0.72rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--fs-text-muted);">QUICK ACTIONS</h2>
    <div class="row g-3">
        <div class="col-sm-4">
            <a id="btn-add-listing" href="add_listing.php" class="quick-action">
                <div class="qa-icon">➕</div>
                <div>
                    <div style="font-weight:700;">Add New Listing</div>
                    <div style="font-size:0.78rem; color:var(--fs-text-muted);">Post surplus food</div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a id="btn-my-listings" href="my_listings.php" class="quick-action">
                <div class="qa-icon">📋</div>
                <div>
                    <div style="font-weight:700;">My Listings</div>
                    <div style="font-size:0.78rem; color:var(--fs-text-muted);">View &amp; manage</div>
                </div>
            </a>
        </div>
        <div class="col-sm-4">
            <a id="btn-manage-reservations" href="manage_reservations.php" class="quick-action">
                <div class="qa-icon">📅</div>
                <div>
                    <div style="font-weight:700;">Reservations</div>
                    <div style="font-size:0.78rem; color:var(--fs-text-muted);">Confirm &amp; collect</div>
                </div>
            </a>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

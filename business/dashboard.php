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
require_once '../includes/header.php';
?>

<main>
<div class="container py-4">
    <h1 class="h3 fw-bold mb-1">Welcome, <?= htmlspecialchars($business['business_name'], ENT_QUOTES, 'UTF-8') ?> 👋</h1>
    <p class="text-muted mb-4">Here's a summary of your FoodSaver activity.</p>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-4 text-success">📋</div>
                    <div>
                        <div class="display-6 fw-bold text-success"><?= $active_listings ?></div>
                        <div class="text-muted">Active Listings</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="display-4">🔔</div>
                    <div>
                        <div class="display-6 fw-bold text-warning"><?= $pending_reservations ?></div>
                        <div class="text-muted">Pending Reservations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="h5 fw-bold mb-3">Quick Actions</h2>
    <div class="d-flex flex-wrap gap-3">
        <a id="btn-add-listing" href="add_listing.php" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Add New Listing
        </a>
        <a id="btn-my-listings" href="my_listings.php" class="btn btn-outline-success btn-lg">
            <i class="bi bi-list-ul me-2"></i>View My Listings
        </a>
        <a id="btn-manage-reservations" href="manage_reservations.php" class="btn btn-outline-warning btn-lg">
            <i class="bi bi-calendar-check me-2"></i>Manage Reservations
        </a>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

<?php
/**
 * Shared HTML <head> + Bootstrap 5.3 navigation bar.
 *
 * The calling page MUST:
 *  1. Call session_start() before including this file.
 *  2. Set $page_title (string) before including this file.
 *  3. Optionally set $active_page (string) to highlight the correct nav link.
 *
 * Helper function get_urgency_badge_html() is defined here for reuse
 * across browse_listings.php, my_listings.php, my_reservations.php, etc.
 */

/**
 * Return a Bootstrap urgency badge HTML string.
 */
function get_urgency_badge_html(string $urgency): string {
    switch ($urgency) {
        case 'high':
            return '<span class="badge bg-danger">High Urgency</span>';
        case 'medium':
            return '<span class="badge bg-warning text-dark">Medium</span>';
        default:
            return '<span class="badge bg-secondary">Low</span>';
    }
}

$page_title  = $page_title  ?? 'FoodSaver';
$active_page = $active_page ?? '';

$role     = $_SESSION['role']    ?? null;
$username = htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FoodSaver — connecting food businesses with the community to reduce food waste in Sri Lanka. Browse surplus food listings and reserve before they expire.">
    <title>FoodSaver — <?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Bootstrap 5.3 CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { padding-top: 70px; }
        .navbar-brand { font-weight: 700; font-size: 1.3rem; }
        .urgency-high   { border-left: 4px solid var(--bs-danger)  !important; }
        .urgency-medium { border-left: 4px solid var(--bs-warning) !important; }
        .urgency-low    { border-left: 4px solid var(--bs-secondary) !important; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= ($role === 'business') ? '../index.php' : (($role === 'admin') ? '../index.php' : 'index.php') ?>">🍱 FoodSaver</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
<?php if ($role === null): // Not logged in ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'home' ? 'active' : '' ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'browse' ? 'active' : '' ?>" href="browse_listings.php">Browse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'login' ? 'active' : '' ?>" href="auth/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-light btn-sm ms-2 px-3" href="auth/register.php">Register</a>
                </li>
<?php elseif ($role === 'customer'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'browse' ? 'active' : '' ?>" href="browse_listings.php">Browse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'reservations' ? 'active' : '' ?>" href="my_reservations.php">My Reservations</a>
                </li>
                <li class="nav-item ms-2">
                    <span class="navbar-text text-white-50 me-2 small"><i class="bi bi-person-circle me-1"></i><?= $username ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm px-3" href="logout.php">Logout</a>
                </li>
<?php elseif ($role === 'business'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="../business/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'add_listing' ? 'active' : '' ?>" href="../business/add_listing.php">Add Listing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'my_listings' ? 'active' : '' ?>" href="../business/my_listings.php">My Listings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'manage_reservations' ? 'active' : '' ?>" href="../business/manage_reservations.php">Reservations</a>
                </li>
                <li class="nav-item ms-2">
                    <span class="navbar-text text-white-50 me-2 small"><i class="bi bi-shop me-1"></i><?= $username ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm px-3" href="../logout.php">Logout</a>
                </li>
<?php elseif ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="../admin/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item ms-2">
                    <span class="navbar-text text-white-50 me-2 small"><i class="bi bi-shield-lock me-1"></i><?= $username ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm px-3" href="../logout.php">Logout</a>
                </li>
<?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

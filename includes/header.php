<?php
/**
 * Shared HTML <head> + FoodSaver premium navigation bar.
 *
 * Calling page MUST:
 *  1. Call session_start() before including this file.
 *  2. Set $page_title (string) before including.
 *  3. Optionally set $active_page (string) for nav highlighting.
 *  4. Optionally set $css_prefix (string) for subdirectory asset paths.
 */

function get_urgency_badge_html(string $urgency): string {
    return match($urgency) {
        'high'   => '<span class="badge-urgency-high">🔴 High Urgency</span>',
        'medium' => '<span class="badge-urgency-medium">🟡 Medium</span>',
        default  => '<span class="badge-urgency-low">Low</span>',
    };
}

$page_title  = $page_title  ?? 'FoodSaver';
$active_page = $active_page ?? '';
$css_prefix  = $css_prefix  ?? '';   // '' for root, '../' for subdirs

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
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FoodSaver Premium CSS -->
    <link rel="stylesheet" href="<?= $css_prefix ?>assets/css/style.css">
</head>
<body>

<nav class="fs-navbar navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $css_prefix ?>index.php">
            🍱 FoodSaver
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"
                style="color:rgba(255,255,255,0.8);">
            <i class="bi bi-list" style="font-size:1.4rem;"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">

<?php if ($role === null): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'home' ? 'active' : '' ?>" href="<?= $css_prefix ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'browse' ? 'active' : '' ?>" href="<?= $css_prefix ?>browse_listings.php">Browse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'login' ? 'active' : '' ?>" href="<?= $css_prefix ?>auth/login.php">Login</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn-nav-primary" href="<?= $css_prefix ?>auth/register.php">Register</a>
                </li>

<?php elseif ($role === 'customer'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'browse' ? 'active' : '' ?>" href="<?= $css_prefix ?>browse_listings.php">Browse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'reservations' ? 'active' : '' ?>" href="<?= $css_prefix ?>my_reservations.php">My Reservations</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <span class="user-chip"><i class="bi bi-person-circle"></i><?= $username ?></span>
                </li>
                <li class="nav-item ms-lg-1">
                    <a class="btn-nav-outline" href="<?= $css_prefix ?>logout.php">Logout</a>
                </li>

<?php elseif ($role === 'business'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="<?= $css_prefix ?>business/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'add_listing' ? 'active' : '' ?>" href="<?= $css_prefix ?>business/add_listing.php">Add Listing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'my_listings' ? 'active' : '' ?>" href="<?= $css_prefix ?>business/my_listings.php">My Listings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'manage_reservations' ? 'active' : '' ?>" href="<?= $css_prefix ?>business/manage_reservations.php">Reservations</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <span class="user-chip"><i class="bi bi-shop"></i><?= $username ?></span>
                </li>
                <li class="nav-item ms-lg-1">
                    <a class="btn-nav-outline" href="<?= $css_prefix ?>logout.php">Logout</a>
                </li>

<?php elseif ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="<?= $css_prefix ?>admin/dashboard.php">Admin Dashboard</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <span class="user-chip"><i class="bi bi-shield-lock"></i><?= $username ?></span>
                </li>
                <li class="nav-item ms-lg-1">
                    <a class="btn-nav-outline" href="<?= $css_prefix ?>logout.php">Logout</a>
                </li>

<?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

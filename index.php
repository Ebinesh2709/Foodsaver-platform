<?php
session_start();

// Redirect logged-in users to their dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'business':
            header('Location: business/dashboard.php');
            exit;
        case 'admin':
            header('Location: admin/dashboard.php');
            exit;
        default: // customer
            header('Location: browse_listings.php');
            exit;
    }
}

$page_title  = 'Save Food, Feed Community';
$active_page = 'home';
require_once 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="bg-success text-white py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-4 fw-bold mb-3">Save Food,<br>Feed Community 🍱</h1>
                    <p class="lead mb-4">
                        FoodSaver connects local restaurants, canteens and bakeries with people in the community
                        — turning surplus food into second chances before it becomes waste.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a id="btn-browse" href="browse_listings.php" class="btn btn-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-search me-2"></i>Browse Listings
                        </a>
                        <a id="btn-register-business" href="auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-shop me-2"></i>Register as Business
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center mt-4 mt-lg-0">
                    <span style="font-size: 9rem; line-height: 1;">🥘</span>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">How It Works</h2>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 py-4 px-3">
                        <div class="card-body">
                            <div class="display-3 mb-3">🏪</div>
                            <h3 class="h5 fw-bold">1. Business Posts</h3>
                            <p class="text-muted">Restaurants and food businesses list their surplus food with a discounted price and pickup window. Our AI automatically scores urgency.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 py-4 px-3">
                        <div class="card-body">
                            <div class="display-3 mb-3">🔍</div>
                            <h3 class="h5 fw-bold">2. Customer Finds</h3>
                            <p class="text-muted">Browse available listings or use our AI-powered natural language search. Find great food nearby before it expires.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 py-4 px-3">
                        <div class="card-body">
                            <div class="display-3 mb-3">🌿</div>
                            <h3 class="h5 fw-bold">3. Food Saved</h3>
                            <p class="text-muted">The customer reserves and collects the food during the pickup window. Less waste, better value, stronger community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SDG 12 Section -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">🌍 Supporting UN SDG 12</h2>
            <p class="lead text-muted mb-4">
                FoodSaver directly addresses <strong>Sustainable Development Goal 12 — Responsible Consumption and Production</strong>
                by reducing food waste at the community level in Sri Lanka and beyond.
            </p>
            <a href="auth/register.php" class="btn btn-success btn-lg px-5">
                Join FoodSaver Today
            </a>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

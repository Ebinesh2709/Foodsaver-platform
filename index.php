<?php
session_start();

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'business': header('Location: business/dashboard.php'); exit;
        case 'admin':    header('Location: admin/dashboard.php');    exit;
        default:         header('Location: browse_listings.php');    exit;
    }
}

$page_title  = 'Save Food, Feed Community';
$active_page = 'home';
$css_prefix  = '';
require_once 'includes/header.php';
?>

<!-- ═══ HERO ═══ -->
<section class="fs-hero">
    <div class="hero-blob"></div>
    <div class="container hero-content">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <p class="section-label" style="color:rgba(255,255,255,0.6); margin-bottom:0.75rem;">
                    🌍 Sustainable Food Platform · Sri Lanka
                </p>
                <h1>Save Food,<br>Feed Community</h1>
                <p class="hero-sub">
                    FoodSaver connects restaurants, canteens and bakeries with people in the community —
                    turning surplus food into second chances before it becomes waste.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a id="btn-browse" href="browse_listings.php" class="btn btn-fs-white btn-lg px-4 fw-bold">
                        <i class="bi bi-search me-2"></i>Browse Listings
                    </a>
                    <a id="btn-register-business" href="auth/register.php" class="btn btn-fs-white-outline btn-lg px-4">
                        <i class="bi bi-shop me-2"></i>Register as Business
                    </a>
                </div>
                <div class="mt-4 d-flex gap-4">
                    <div style="color:rgba(255,255,255,0.75); font-size:0.82rem;">
                        <span style="font-size:1.4rem; font-weight:800; color:#fff; display:block;">500+</span>
                        Meals Saved
                    </div>
                    <div style="width:1px; background:rgba(255,255,255,0.2);"></div>
                    <div style="color:rgba(255,255,255,0.75); font-size:0.82rem;">
                        <span style="font-size:1.4rem; font-weight:800; color:#fff; display:block;">50+</span>
                        Businesses
                    </div>
                    <div style="width:1px; background:rgba(255,255,255,0.2);"></div>
                    <div style="color:rgba(255,255,255,0.75); font-size:0.82rem;">
                        <span style="font-size:1.4rem; font-weight:800; color:#fff; display:block;">AI</span>
                        Powered Search
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-emoji">🥘</div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ HOW IT WORKS ═══ -->
<section class="page-section" style="background:var(--fs-bg);">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label">Simple Process</p>
            <h2 style="font-size:1.9rem; font-weight:800; letter-spacing:-0.03em;">How It Works</h2>
            <p class="text-muted" style="max-width:480px; margin:0.5rem auto 0; font-size:0.9rem;">Three easy steps to connect surplus food with people who need it.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-num">1</span>
                    <span class="step-icon">🏪</span>
                    <h3>Business Posts</h3>
                    <p>Restaurants and food businesses list surplus food with a discounted price and pickup window. Our AI automatically scores urgency.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-num">2</span>
                    <span class="step-icon">🔍</span>
                    <h3>Customer Finds</h3>
                    <p>Browse available listings or use AI-powered natural language search. Find great food nearby before it expires.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <span class="step-num">3</span>
                    <span class="step-icon">🌿</span>
                    <h3>Food Saved</h3>
                    <p>The customer reserves and collects during the pickup window. Less waste, better value, stronger community.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ SDG 12 ═══ -->
<section class="sdg-section page-section">
    <div class="container text-center">
        <div style="font-size:3.5rem; margin-bottom:1rem;">🌍</div>
        <p class="section-label">United Nations Goals</p>
        <h2 style="font-size:1.8rem; font-weight:800; letter-spacing:-0.03em; margin-bottom:0.75rem;">Supporting UN SDG 12</h2>
        <p class="text-muted" style="max-width:560px; margin:0 auto 2rem; font-size:0.9rem;">
            FoodSaver directly addresses <strong>Sustainable Development Goal 12 — Responsible Consumption and Production</strong>
            by reducing food waste at the community level in Sri Lanka.
        </p>
        <a href="auth/register.php" class="btn btn-fs-primary btn-lg px-5">
            <i class="bi bi-person-plus me-2"></i>Join FoodSaver Today
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

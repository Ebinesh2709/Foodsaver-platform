<?php
session_start();
http_response_code(404);

$page_title  = 'Page Not Found';
$css_prefix  = '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSaver — Page Not Found</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body { padding-top: 0; min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg,#e8f5ee 0%,#f0f9f4 60%,#fff 100%); }
        .err-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; }
    </style>
</head>
<body>

<nav class="fs-navbar navbar" style="position:relative; backdrop-filter:none;">
    <div class="container">
        <a class="navbar-brand" href="/index.php">🍱 FoodSaver</a>
    </div>
</nav>

<div class="err-wrap">
    <div class="text-center" style="max-width:480px;">
        <div style="font-size:5rem; margin-bottom:1rem;">🍽️</div>
        <h1 style="font-size:5rem; font-weight:800; color:var(--fs-green); line-height:1; letter-spacing:-0.04em;">404</h1>
        <h2 style="font-size:1.4rem; font-weight:700; margin-bottom:0.75rem;">This page has moved or doesn't exist</h2>
        <p style="color:var(--fs-text-muted); font-size:0.9rem; margin-bottom:2rem;">
            The old HTML pages have been replaced with a new and improved PHP platform. Use the links below to get back on track.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="/index.php" class="btn btn-fs-primary px-4">
                <i class="bi bi-house me-2"></i>Go Home
            </a>
            <a href="/browse_listings.php" class="btn btn-fs-outline px-4">
                <i class="bi bi-search me-2"></i>Browse Food
            </a>
            <a href="/auth/login.php" class="btn btn-fs-outline px-4">
                <i class="bi bi-person me-2"></i>Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'business': header('Location: ../business/dashboard.php'); exit;
        case 'admin':    header('Location: ../admin/dashboard.php');    exit;
        default:         header('Location: ../browse_listings.php');    exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'Invalid email or password.';
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        switch ($user['role']) {
            case 'business': header('Location: ../business/dashboard.php'); exit;
            case 'admin':    header('Location: ../admin/dashboard.php');    exit;
            default:         header('Location: ../browse_listings.php');    exit;
        }
    }
}

$css_prefix  = '../';
$page_title  = 'Login';
$active_page = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to your FoodSaver account.">
    <title>FoodSaver — Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { padding-top: 0; min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg,#e8f5ee 0%,#f0f9f4 60%,#fff 100%); }
        .auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
    </style>
</head>
<body>

<!-- Minimal nav for auth pages -->
<nav class="fs-navbar navbar" style="position:relative; backdrop-filter:none;">
    <div class="container">
        <a class="navbar-brand" href="../index.php">🍱 FoodSaver</a>
        <a href="register.php" class="btn-nav-primary">Create Account</a>
    </div>
</nav>

<div class="auth-wrap">
    <div style="width:100%; max-width:420px;">
        <div class="fs-form-card">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem; margin-bottom:0.5rem;">👋</div>
                <h1 class="fs-form-heading">Welcome Back</h1>
                <p class="fs-form-sub">Login to your FoodSaver account</p>
            </div>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                <div class="fs-alert-success mb-3">
                    <i class="bi bi-check-circle me-2"></i>Account created! Please log in.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="fs-alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" id="login-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus
                           placeholder="you@example.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="••••••••">
                </div>

                <button type="submit" id="btn-login-submit" class="btn btn-fs-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <hr class="divider">
            <p class="text-center text-muted mb-0" style="font-size:0.875rem;">
                Don't have an account? <a href="register.php" style="color:var(--fs-green); font-weight:600;">Register here</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmkfh6kvJBiA5fE2RkMFeAx1yAX"
        crossorigin="anonymous"></script>
</body>
</html>

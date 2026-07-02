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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍱</text></svg>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { padding-top: 0; min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg, var(--fs-bg) 0%, #f4f1ea 100%); }
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

                <button type="submit" id="btn-login-submit" class="btn btn-fs-primary w-100 py-2 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <a href="phone_login.php" class="btn btn-outline-secondary w-100 py-2 mb-3">
                <i class="bi bi-phone me-2"></i>Login with Phone (OTP)
            </a>

            <a href="google_callback.php?mock_login=1" class="btn btn-outline-dark w-100 py-2 mb-3 d-flex align-items-center justify-content-center">
                <svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                  <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.73 17.74 9.5 24 9.5z"/>
                  <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                  <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                  <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Continue with Google
            </a>

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

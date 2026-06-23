<?php
session_start();

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';

// Redirect if already logged in
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

    // Find user by email
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
    <style>
        body { background: #f0fdf4; }
        .card { border-radius: 1rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">🍱 FoodSaver</a>
        <a href="register.php" class="btn btn-outline-light btn-sm">Register</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow p-4 p-md-5">
                <h1 class="h3 fw-bold text-success mb-1">Welcome Back</h1>
                <p class="text-muted mb-4">Login to your FoodSaver account.</p>

                <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>Account created successfully! Please log in.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="login.php" id="login-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" id="btn-login-submit" class="btn btn-success w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>

                <hr>
                <p class="text-center text-muted mb-0">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmkfh6kvJBiA5fE2RkMFeAx1yAX"
        crossorigin="anonymous"></script>
</body>
</html>

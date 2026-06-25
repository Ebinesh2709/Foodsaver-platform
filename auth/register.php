<?php
session_start();

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';
require_once '../includes/validation_helpers.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'business': header('Location: ../business/dashboard.php'); exit;
        case 'admin':    header('Location: ../admin/dashboard.php');    exit;
        default:         header('Location: ../browse_listings.php');    exit;
    }
}

$errors = [];
$old    = []; // repopulate form values on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // Sanitise inputs
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $phone    = trim($_POST['phone']    ?? '');
    $role     = trim($_POST['role']     ?? '');

    // Business-specific
    $biz_name = trim($_POST['business_name'] ?? '');
    $biz_addr = trim($_POST['address']       ?? '');
    $biz_area = trim($_POST['area']          ?? '');
    $biz_desc = trim($_POST['description']   ?? '');

    $old = compact('name','email','phone','role','biz_name','biz_addr','biz_area','biz_desc');

    // --- Server-side validation ---
    if ($name === '' || mb_strlen($name) > 100) {
        $errors['name'] = 'Full name is required (max 100 characters).';
    }
    if (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already registered.';
        }
    }
    if (!validate_password($password)) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if (!in_array($role, ['customer', 'business'], true)) {
        $errors['role'] = 'Please select a valid role (Customer or Business).';
    }
    if ($role === 'business' && $biz_name === '') {
        $errors['business_name'] = 'Business name is required.';
    }

    // --- Process if no errors ---
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'business') {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $email, $hashed, $role, $phone]);
                $user_id = (int)$pdo->lastInsertId();

                $stmt2 = $pdo->prepare('INSERT INTO businesses (user_id, business_name, address, area, description) VALUES (?, ?, ?, ?, ?)');
                $stmt2->execute([$user_id, $biz_name, $biz_addr, $biz_area, $biz_desc]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors['general'] = 'Registration failed. Please try again.';
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hashed, $role, $phone]);
        }

        if (empty($errors)) {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

$page_title  = 'Create Account';
$active_page = 'register';
// header.php is at root level from auth/ so path is relative to auth/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create a FoodSaver account as a customer or food business.">
    <title>FoodSaver — Create Account</title>
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
        <div>
            <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow p-4 p-md-5">
                <h1 class="h3 fw-bold text-success mb-1">Create Account</h1>
                <p class="text-muted mb-4">Join FoodSaver to reduce food waste in your community.</p>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="register.php" id="register-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required maxlength="100">
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               required minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Phone <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="20">
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">I am registering as a</label>
                        <?php if (isset($errors['role'])): ?>
                            <div class="text-danger small mb-1"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="role_customer" value="customer"
                                    <?= (($old['role'] ?? 'customer') === 'customer') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_customer">👤 Customer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="role_business" value="business"
                                    <?= (($old['role'] ?? '') === 'business') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_business">🏪 Business</label>
                            </div>
                        </div>
                    </div>

                    <!-- Business Fields (shown only when business role selected) -->
                    <div id="business-fields" class="border rounded p-3 mb-3 bg-light" style="display: <?= (($old['role'] ?? '') === 'business') ? 'block' : 'none' ?>;">
                        <h2 class="h6 fw-bold text-success mb-3">Business Details</h2>

                        <div class="mb-3">
                            <label for="business_name" class="form-label fw-semibold">Business Name</label>
                            <input type="text" id="business_name" name="business_name"
                                   class="form-control <?= isset($errors['business_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['biz_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (isset($errors['business_name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['business_name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="area" class="form-label fw-semibold">Area / Neighbourhood</label>
                            <input type="text" id="area" name="area" class="form-control"
                                   value="<?= htmlspecialchars($old['biz_area'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Colombo 3">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Full Address</label>
                            <input type="text" id="address" name="address" class="form-control"
                                   value="<?= htmlspecialchars($old['biz_addr'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="mb-2">
                            <label for="description" class="form-label fw-semibold">Short Description</label>
                            <textarea id="description" name="description" class="form-control" rows="2"
                                      placeholder="e.g. A bakery specialising in artisan bread and pastries"><?= htmlspecialchars($old['biz_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" id="btn-register-submit" class="btn btn-success w-100 py-2 fw-semibold">
                        Create Account
                    </button>
                </form>

                <hr>
                <p class="text-center text-muted mb-0">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmkfh6kvJBiA5fE2RkMFeAx1yAX"
        crossorigin="anonymous"></script>
<script>
    // Show/hide business fields based on role selection
    const radios = document.querySelectorAll('input[name="role"]');
    const bizFields = document.getElementById('business-fields');
    radios.forEach(r => r.addEventListener('change', () => {
        bizFields.style.display = (r.value === 'business' && r.checked) ? 'block' : bizFields.style.display;
        if (r.value === 'customer' && r.checked) bizFields.style.display = 'none';
        if (r.value === 'business' && r.checked) bizFields.style.display = 'block';
    }));
</script>
</body>
</html>

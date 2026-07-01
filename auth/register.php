<?php
session_start();

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';
require_once '../includes/validation_helpers.php';

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'business': header('Location: ../business/dashboard.php'); exit;
        case 'admin':    header('Location: ../admin/dashboard.php');    exit;
        default:         header('Location: ../browse_listings.php');    exit;
    }
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $phone    = trim($_POST['phone']    ?? '');
    $role     = trim($_POST['role']     ?? '');

    $biz_name = trim($_POST['business_name'] ?? '');
    $biz_addr = trim($_POST['address']       ?? '');
    $biz_area = trim($_POST['area']          ?? '');
    $biz_desc = trim($_POST['description']   ?? '');

    $old = compact('name','email','phone','role','biz_name','biz_addr','biz_area','biz_desc');

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
        $errors['role'] = 'Please select a valid role.';
    }
    if ($role === 'business' && $biz_name === '') {
        $errors['business_name'] = 'Business name is required.';
    }

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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { padding-top: 0; background: linear-gradient(135deg, var(--fs-bg) 0%, #f4f1ea 100%); }
        .auth-wrap { padding: 2rem 1rem 3rem; }
    </style>
</head>
<body>

<nav class="fs-navbar navbar" style="position:relative; backdrop-filter:none;">
    <div class="container">
        <a class="navbar-brand" href="../index.php">🍱 FoodSaver</a>
        <a href="login.php" class="btn-nav-outline">Login</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="fs-form-card">
                    <div class="text-center mb-4">
                        <div style="font-size:2.5rem; margin-bottom:0.5rem;">✨</div>
                        <h1 class="fs-form-heading">Create Account</h1>
                        <p class="fs-form-sub">Join FoodSaver to reduce food waste in your community</p>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="fs-alert-danger mb-3">
                            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="register.php" id="register-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" maxlength="100" required
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="Your full name">
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="you@example.com">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   placeholder="Minimum 8 characters">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control" maxlength="20"
                                   value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="+94 77 000 0000">
                        </div>

                        <!-- Role Selector -->
                        <div class="mb-3">
                            <label class="form-label d-block">I am registering as</label>
                            <?php if (isset($errors['role'])): ?>
                                <div class="text-danger small mb-2"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <div class="d-flex gap-3">
                                <label class="flex-fill" style="cursor:pointer;">
                                    <input type="radio" name="role" id="role_customer" value="customer" class="d-none"
                                           <?= (($old['role'] ?? 'customer') === 'customer') ? 'checked' : '' ?>>
                                    <div class="role-option p-3 border rounded text-center" id="lbl-customer"
                                         style="border-radius:var(--fs-radius)!important; transition:var(--fs-transition); font-weight:600; font-size:0.875rem;">
                                        👤<div>Customer</div>
                                    </div>
                                </label>
                                <label class="flex-fill" style="cursor:pointer;">
                                    <input type="radio" name="role" id="role_business" value="business" class="d-none"
                                           <?= (($old['role'] ?? '') === 'business') ? 'checked' : '' ?>>
                                    <div class="role-option p-3 border rounded text-center" id="lbl-business"
                                         style="border-radius:var(--fs-radius)!important; transition:var(--fs-transition); font-weight:600; font-size:0.875rem;">
                                        🏪<div>Business</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Business Fields -->
                        <div id="business-fields" class="biz-fields-panel" style="display:<?= (($old['role'] ?? '') === 'business') ? 'block' : 'none' ?>;">
                            <h2>Business Details</h2>
                            <div class="mb-3">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" id="business_name" name="business_name"
                                       class="form-control <?= isset($errors['business_name']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['biz_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Your restaurant or business name">
                                <?php if (isset($errors['business_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['business_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="area" class="form-label">Area</label>
                                    <input type="text" id="area" name="area" class="form-control"
                                           value="<?= htmlspecialchars($old['biz_area'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="e.g. Colombo 3">
                                </div>
                                <div class="col-sm-6">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" id="address" name="address" class="form-control"
                                           value="<?= htmlspecialchars($old['biz_addr'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="description" class="form-label">Short Description</label>
                                <textarea id="description" name="description" class="form-control" rows="2"
                                          placeholder="e.g. A bakery specialising in artisan bread"><?= htmlspecialchars($old['biz_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>

                        <button type="submit" id="btn-register-submit" class="btn btn-fs-primary w-100 py-2 mt-2">
                            <i class="bi bi-person-check me-2"></i>Create Account
                        </button>
                    </form>

                    <hr class="divider">
                    <p class="text-center text-muted mb-0" style="font-size:0.875rem;">
                        Already have an account? <a href="login.php" style="color:var(--fs-green); font-weight:600;">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmkfh6kvJBiA5fE2RkMFeAx1yAX"
        crossorigin="anonymous"></script>
<script>
const radios     = document.querySelectorAll('input[name="role"]');
const bizFields  = document.getElementById('business-fields');
const lblCust    = document.getElementById('lbl-customer');
const lblBiz     = document.getElementById('lbl-business');

function updateRoleUI() {
    const val = document.querySelector('input[name="role"]:checked')?.value;
    const sel = 'border-color:var(--fs-green)!important; background:var(--fs-green-light); color:var(--fs-green-dark);';
    const unsel = '';
    lblCust.style.cssText = (val === 'customer') ? sel : unsel;
    lblBiz.style.cssText  = (val === 'business')  ? sel : unsel;
    bizFields.style.display = (val === 'business') ? 'block' : 'none';
}

radios.forEach(r => r.addEventListener('change', updateRoleUI));
updateRoleUI();
</script>
</body>
</html>

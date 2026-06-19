<?php
session_start();
define('APP_RUNNING', true);
require '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name']);
    $email         = trim($_POST['email']);
    $password      = $_POST['password'];
    $role          = $_POST['role'];
    $phone         = trim($_POST['phone']);
    $business_name = trim($_POST['business_name'] ?? '');

    if (!$name || !$email || !$password || !$role) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed, $role, $phone]);
            $userId = $pdo->lastInsertId();

            if ($role === 'business') {
                $stmt2 = $pdo->prepare("INSERT INTO businesses (user_id, business_name) VALUES (?, ?)");
                $stmt2->execute([$userId, $business_name]);
            }

            header("Location: login.php?registered=1");
            exit;
        }
    }
}

include '../includes/header.php';
?>

<h2>Create an Account</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">I am registering as</label>
        <select name="role" class="form-select" id="roleSelect" required>
            <option value="customer">Customer</option>
            <option value="business">Business</option>
        </select>
    </div>
    <div class="mb-3" id="businessNameField" style="display:none;">
        <label class="form-label">Business Name</label>
        <input type="text" name="business_name" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Register</button>
</form>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('businessNameField').style.display = 
        this.value === 'business' ? 'block' : 'none';
});
</script>

<?php include '../includes/footer.php'; ?>
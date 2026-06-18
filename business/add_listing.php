<?php
require '../includes/auth_check.php';
require_role('business');
require '../config/db.php';

$stmt = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$business = $stmt->fetch();
$business_id = $business['id'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $original_price = $_POST['original_price'];
    $discounted_price = $_POST['discounted_price'];
    $quantity = $_POST['quantity'];
    $pickup_start = $_POST['pickup_start'];
    $pickup_end = $_POST['pickup_end'];
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $_FILES['image']['size'] < 2000000) {
            $imageName = time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../assets/uploads/' . $imageName);
        } else {
            $error = "Image must be jpg, png, or webp and under 2MB.";
        }
    }

    if (!$error && $title && $quantity) {
        $stmt = $pdo->prepare("INSERT INTO food_listings (business_id, title, description, category, original_price, discounted_price, quantity, pickup_start, pickup_end, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')");
        $stmt->execute([$business_id, $title, $description, $category, $original_price, $discounted_price, $quantity, $pickup_start, $pickup_end, $imageName]);
        header("Location: dashboard.php");
        exit;
    } elseif (!$error) {
        $error = "Please fill in the required fields.";
    }
}

include '../includes/header.php';
?>

<h2>Add Food Listing</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" placeholder="e.g. Bakery, Meals, Produce">
    </div>
    <div class="mb-3">
        <label class="form-label">Original Price (Rs.)</label>
        <input type="number" step="0.01" name="original_price" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Discounted Price (Rs.)</label>
        <input type="number" step="0.01" name="discounted_price" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity Available</label>
        <input type="number" name="quantity" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Pickup Start</label>
        <input type="datetime-local" name="pickup_start" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Pickup End</label>
        <input type="datetime-local" name="pickup_end" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-success">Add Listing</button>
</form>

<?php include '../includes/footer.php'; ?>
<?php
session_start();
define('APP_RUNNING', true);
require_once '../includes/csrf_helper.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php");
    exit;
}
$stmt_biz = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt_biz->execute([$_SESSION['user_id']]);
$business = $stmt_biz->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    die("No business profile found. Please complete your business registration first.");
}
$business_id = $business['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $title           = trim($_POST['title'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $category        = trim($_POST['category'] ?? '');
    $quantity        = trim($_POST['quantity'] ?? '');
    $original_price  = trim($_POST['original_price'] ?? '');
    $discounted_price= trim($_POST['discounted_price'] ?? '');
    $pickup_start    = trim($_POST['pickup_start'] ?? '');
    $pickup_end      = trim($_POST['pickup_end'] ?? '');

    if ($title === '') $errors[] = "Title is required.";
    if ($quantity === '' || !is_numeric($quantity) || $quantity <= 0) $errors[] = "Quantity must be a positive number.";
    if ($pickup_start === '' || $pickup_end === '') $errors[] = "Pickup times are required.";
    if ($pickup_end <= $pickup_start) $errors[] = "Pickup end must be after pickup start.";

    if (empty($errors)) {
        // Handle image upload
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
            } else {
                $errors[] = "Image must be jpg, jpeg, png, or webp.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO food_listings (business_id, title, description, category, quantity, original_price, discounted_price, pickup_start, pickup_end, image, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')"
            );
            $stmt->execute([$business_id, $title, $description, $category, $quantity, $original_price ?: null, $discounted_price ?: null, $pickup_start, $pickup_end, $image]);
            $new_listing_id = $pdo->lastInsertId();

            // AI urgency scoring - comment out if ai_helper.php not built yet
            if (file_exists('../includes/ai_helper.php')) {
                require_once '../includes/ai_helper.php';
                $urgency = get_urgency_score($description, $pickup_end);
                $update = $pdo->prepare("UPDATE food_listings SET urgency_score = ? WHERE id = ?");
                $update->execute([$urgency, $new_listing_id]);
            }

            header("Location: my_listings.php?success=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Add Listing</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Post a Food Listing</h2>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="POST" enctype="multipart/form-data">
  <div class="mb-3"><label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3"></textarea></div>
  <div class="mb-3"><label class="form-label">Category</label>
    <select name="category" class="form-select">
      <option value="">-- Select --</option>
      <option value="meals">Meals</option>
      <option value="bakery">Bakery</option>
      <option value="produce">Produce</option>
      <option value="dairy">Dairy</option>
      <option value="other">Other</option>
    </select></div>
  <div class="row">
    <div class="col mb-3"><label class="form-label">Original Price (LKR)</label>
      <input type="number" step="0.01" name="original_price" class="form-control"></div>
    <div class="col mb-3"><label class="form-label">Discounted Price (LKR)</label>
      <input type="number" step="0.01" name="discounted_price" class="form-control"></div>
  </div>
  <div class="mb-3"><label class="form-label">Quantity</label>
    <input type="number" name="quantity" class="form-control" required></div>
  <div class="row">
    <div class="col mb-3"><label class="form-label">Pickup Start</label>
      <input type="datetime-local" name="pickup_start" class="form-control" required></div>
    <div class="col mb-3"><label class="form-label">Pickup End</label>
      <input type="datetime-local" name="pickup_end" class="form-control" required></div>
  </div>
  <div class="mb-3"><label class="form-label">Food Photo (optional)</label>
    <input type="file" name="image" class="form-control" accept="image/*"></div>
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">  
  <button class="btn btn-primary">Post Listing</button>
  <a href="my_listings.php" class="btn btn-secondary">Cancel</a>
</form>
</div></body></html>
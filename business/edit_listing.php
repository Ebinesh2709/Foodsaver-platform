<?php
session_start();
define('APP_RUNNING', true);
require_once '../includes/csrf_helper.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$stmt_biz = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt_biz->execute([$_SESSION['user_id']]);
$business = $stmt_biz->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    die("No business profile found. Please complete your business registration first.");
}
$business_id = $business['id'];
$listing_id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$listing_id) die("No listing specified.");

$stmt = $pdo->prepare("SELECT * FROM food_listings WHERE id = ? AND business_id = ?");
$stmt->execute([$listing_id, $business_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$listing) die("Listing not found or not yours.");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $title            = trim($_POST['title'] ?? '');
    $description      = trim($_POST['description'] ?? '');
    $category         = trim($_POST['category'] ?? '');
    $quantity         = trim($_POST['quantity'] ?? '');
    $original_price   = trim($_POST['original_price'] ?? '');
    $discounted_price = trim($_POST['discounted_price'] ?? '');
    $pickup_start     = trim($_POST['pickup_start'] ?? '');
    $pickup_end       = trim($_POST['pickup_end'] ?? '');

    if ($title === '') $errors[] = "Title is required.";
    if (!is_numeric($quantity) || $quantity <= 0) $errors[] = "Quantity must be valid.";

    if (empty($errors)) {
        $u = $pdo->prepare("UPDATE food_listings SET title=?, description=?, category=?, quantity=?, original_price=?, discounted_price=?, pickup_start=?, pickup_end=? WHERE id=? AND business_id=?");
        $u->execute([$title, $description, $category, $quantity, $original_price ?: null, $discounted_price ?: null, $pickup_start, $pickup_end, $listing_id, $business_id]);
        header("Location: my_listings.php?success=1"); exit;
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Edit Listing</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container mt-4">
<h2>Edit Listing</h2>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="POST">
  <input type="hidden" name="id" value="<?= $listing['id'] ?>">
  <div class="mb-3"><label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($listing['title']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Description</label>
    <textarea name="description" class="form-control"><?= htmlspecialchars($listing['description']) ?></textarea></div>
  <div class="mb-3"><label class="form-label">Category</label>
    <select name="category" class="form-select">
      <option value="">-- Select --</option>
      <?php foreach (['meals','bakery','produce','dairy','other'] as $cat): ?>
      <option value="<?= $cat ?>" <?= $listing['category']===$cat?'selected':'' ?>><?= ucfirst($cat) ?></option>
      <?php endforeach; ?>
    </select></div>
  <div class="row">
    <div class="col mb-3"><label class="form-label">Original Price</label>
      <input type="number" step="0.01" name="original_price" class="form-control" value="<?= htmlspecialchars($listing['original_price']) ?>"></div>
    <div class="col mb-3"><label class="form-label">Discounted Price</label>
      <input type="number" step="0.01" name="discounted_price" class="form-control" value="<?= htmlspecialchars($listing['discounted_price']) ?>"></div>
  </div>
  <div class="mb-3"><label class="form-label">Quantity</label>
    <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($listing['quantity']) ?>" required></div>
  <div class="row">
    <div class="col mb-3"><label class="form-label">Pickup Start</label>
      <input type="datetime-local" name="pickup_start" class="form-control" value="<?= htmlspecialchars(str_replace(' ','T',$listing['pickup_start'])) ?>"></div>
    <div class="col mb-3"><label class="form-label">Pickup End</label>
      <input type="datetime-local" name="pickup_end" class="form-control" value="<?= htmlspecialchars(str_replace(' ','T',$listing['pickup_end'])) ?>"></div>
  </div>
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <button class="btn btn-primary">Save Changes</button>
  <a href="my_listings.php" class="btn btn-secondary">Cancel</a>
</form>
</div></body></html>
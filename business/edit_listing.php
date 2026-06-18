<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$business_id = $_SESSION['user_id'];
$listing_id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$listing_id) die("No listing specified.");

// Always verify ownership before allowing an edit
$stmt = $conn->prepare("SELECT * FROM food_listings WHERE id = ? AND business_id = ?");
$stmt->bind_param("ii", $listing_id, $business_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();
if (!$listing) die("Listing not found or not yours.");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_name = trim($_POST['food_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $expiry_datetime = trim($_POST['expiry_datetime'] ?? '');

    if ($food_name === '') $errors[] = "Food name is required.";
    if (!is_numeric($quantity) || $quantity <= 0) $errors[] = "Quantity must be valid.";

    if (empty($errors)) {
        $u = $conn->prepare("UPDATE food_listings SET food_name=?, description=?, quantity=?, expiry_datetime=? WHERE id=? AND business_id=?");
        $u->bind_param("ssssii", $food_name, $description, $quantity, $expiry_datetime, $listing_id, $business_id);
        $u->execute();
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
  <div class="mb-3"><label class="form-label">Food Name</label>
    <input type="text" name="food_name" class="form-control" value="<?= htmlspecialchars($listing['food_name']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Description</label>
    <textarea name="description" class="form-control"><?= htmlspecialchars($listing['description']) ?></textarea></div>
  <div class="mb-3"><label class="form-label">Quantity</label>
    <input type="number" step="0.1" name="quantity" class="form-control" value="<?= htmlspecialchars($listing['quantity']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Expiry Date & Time</label>
    <input type="datetime-local" name="expiry_datetime" class="form-control" value="<?= htmlspecialchars(str_replace(' ', 'T', $listing['expiry_datetime'])) ?>" required></div>
  <button class="btn btn-primary">Save</button>
  <a href="my_listings.php" class="btn btn-secondary">Cancel</a>
</form>
</div></body></html>
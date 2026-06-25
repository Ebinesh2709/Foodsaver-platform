<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header('Location: ../auth/login.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';
require_once '../includes/urgency_fallback.php';
require_once '../includes/ai_helper.php';

$errors  = [];
$old     = [];
$success = false;

// Get business_id for this user
$stmt = $pdo->prepare('SELECT id FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$biz_row = $stmt->fetch();

if (!$biz_row) {
    header('Location: dashboard.php');
    exit;
}
$business_id = $biz_row['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title          = trim($_POST['title']          ?? '');
    $description    = trim($_POST['description']    ?? '');
    $category       = trim($_POST['category']       ?? '');
    $quantity       = trim($_POST['quantity']       ?? '');
    $original_price = trim($_POST['original_price'] ?? '');
    $disc_price     = trim($_POST['discounted_price'] ?? '');
    $pickup_start   = trim($_POST['pickup_start']   ?? '');
    $pickup_end     = trim($_POST['pickup_end']     ?? '');

    $old = compact('title','description','category','quantity','original_price','disc_price','pickup_start','pickup_end');

    // Validation
    $allowed_categories = ['meals','bakery','produce','dairy','other'];

    if ($title === '' || mb_strlen($title) > 200) {
        $errors['title'] = 'Title is required (max 200 characters).';
    }
    if ($description === '') {
        $errors['description'] = 'Description is required.';
    }
    if (!in_array($category, $allowed_categories, true)) {
        $errors['category'] = 'Please select a valid category.';
    }
    if (!is_numeric($quantity) || (int)$quantity < 1) {
        $errors['quantity'] = 'Quantity must be a whole number greater than 0.';
    }
    if (!is_numeric($original_price) || (float)$original_price < 0) {
        $errors['original_price'] = 'Original price must be a valid number.';
    }
    if (!is_numeric($disc_price) || (float)$disc_price < 0) {
        $errors['discounted_price'] = 'Discounted price must be a valid number.';
    }
    if ($pickup_start === '' || strtotime($pickup_start) === false) {
        $errors['pickup_start'] = 'Please enter a valid pickup start time.';
    }
    if ($pickup_end === '' || strtotime($pickup_end) === false) {
        $errors['pickup_end'] = 'Please enter a valid pickup end time.';
    } elseif (!isset($errors['pickup_start']) && strtotime($pickup_end) <= strtotime($pickup_start)) {
        $errors['pickup_end'] = 'Pickup end must be after pickup start.';
    }

    // Image upload
    $image_filename = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            $errors['image'] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        } elseif (!is_uploaded_file($_FILES['image']['tmp_name'])) {
            $errors['image'] = 'Image upload failed. Please try again.';
        } else {
            $image_filename = uniqid('food_', true) . '.' . $ext;
            $upload_path    = '../uploads/' . $image_filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors['image'] = 'Could not save the uploaded image.';
                $image_filename  = null;
            }
        }
    }

    if (empty($errors)) {
        // Insert with placeholder urgency
        $stmt2 = $pdo->prepare(
            'INSERT INTO food_listings
             (business_id, title, description, category, original_price, discounted_price,
              quantity, pickup_start, pickup_end, image, status, urgency_score)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'available\', \'low\')'
        );
        $stmt2->execute([
            $business_id, $title, $description, $category,
            (float)$original_price, (float)$disc_price,
            (int)$quantity, $pickup_start, $pickup_end, $image_filename,
        ]);
        $new_id = (int)$pdo->lastInsertId();

        // AI urgency scoring and summary generation
        $urgency = get_urgency_score($description, $pickup_end);
        $summary = generate_listing_summary($title, $description, $category, (float)$disc_price, (int)$quantity, $pickup_end);

        $stmt3 = $pdo->prepare('UPDATE food_listings SET urgency_score = ?, ai_summary = ? WHERE id = ?');
        $stmt3->execute([$urgency, $summary, $new_id]);

        header('Location: my_listings.php?added=1');
        exit;
    }
}

$page_title  = 'Add New Listing';
$active_page = 'add_listing';
$css_prefix  = '../';
require_once '../includes/header.php';
?>

<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-plus-circle me-2"></i>Add New Food Listing</h1>
        <p>Fill in the details below — AI will auto-score urgency and generate a summary</p>
    </div>
</div>

<div class="container pb-5">
<div style="max-width:720px; margin:0 auto;">

    <?php if (!empty($errors)): ?>
        <div class="fs-alert-danger mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>Please fix the errors below before submitting.</strong>
        </div>
    <?php endif; ?>

    <form method="post" action="add_listing.php" enctype="multipart/form-data" id="add-listing-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Title</label>
            <input type="text" id="title" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="200" required>
            <?php if (isset($errors['title'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="category" class="form-label fw-semibold">Category</label>
                <select id="category" name="category" class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" required>
                    <option value="">-- Select --</option>
                    <?php foreach (['meals','bakery','produce','dairy','other'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= (($old['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="quantity" class="form-label fw-semibold">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1"
                       class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['quantity'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['quantity'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="original_price" class="form-label fw-semibold">Original Price (LKR)</label>
                <input type="number" step="0.01" min="0" id="original_price" name="original_price"
                       class="form-control <?= isset($errors['original_price']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['original_price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['original_price'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['original_price'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="discounted_price" class="form-label fw-semibold">Discounted Price (LKR)</label>
                <input type="number" step="0.01" min="0" id="discounted_price" name="discounted_price"
                       class="form-control <?= isset($errors['discounted_price']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['disc_price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['discounted_price'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['discounted_price'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="pickup_start" class="form-label fw-semibold">Pickup Start</label>
                <input type="datetime-local" id="pickup_start" name="pickup_start"
                       class="form-control <?= isset($errors['pickup_start']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['pickup_start'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['pickup_start'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['pickup_start'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="pickup_end" class="form-label fw-semibold">Pickup End</label>
                <input type="datetime-local" id="pickup_end" name="pickup_end"
                       class="form-control <?= isset($errors['pickup_end']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($old['pickup_end'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['pickup_end'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['pickup_end'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="image" class="form-label fw-semibold">Food Image <span class="text-muted fw-normal">(optional — JPG, PNG, WEBP)</span></label>
            <input type="file" id="image" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                   accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['image'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-3 pt-2">
            <button type="submit" id="btn-add-listing-submit" class="btn btn-fs-primary px-5 py-2">
                <i class="bi bi-plus-circle me-2"></i>Add Listing
            </button>
            <a href="my_listings.php" class="btn btn-fs-outline px-4 py-2">Cancel</a>
        </div>
    </form>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>

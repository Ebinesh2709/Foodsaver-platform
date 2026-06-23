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

$errors = [];
$old    = [];

// Get business_id
$stmt = $pdo->prepare('SELECT id FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$biz_row = $stmt->fetch();

if (!$biz_row) {
    header('Location: dashboard.php');
    exit;
}
$business_id = $biz_row['id'];

/**
 * Load the listing — verifies ownership via JOIN.
 */
function load_listing(PDO $pdo, int $id, int $business_id): ?array {
    $stmt = $pdo->prepare(
        'SELECT fl.* FROM food_listings fl
         JOIN businesses b ON fl.business_id = b.id
         WHERE fl.id = ? AND b.user_id = ?'
    );
    $stmt->execute([$id, $_SESSION['user_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

$id      = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0);
$listing = load_listing($pdo, $id, $business_id);

if (!$listing) {
    header('Location: my_listings.php');
    exit;
}

// --- POST: save edits ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // Re-verify ownership
    $listing = load_listing($pdo, (int)($_POST['listing_id'] ?? 0), $business_id);
    if (!$listing) {
        header('Location: my_listings.php');
        exit;
    }
    $id = $listing['id'];

    $title          = trim($_POST['title']            ?? '');
    $description    = trim($_POST['description']      ?? '');
    $category       = trim($_POST['category']         ?? '');
    $quantity       = trim($_POST['quantity']         ?? '');
    $original_price = trim($_POST['original_price']   ?? '');
    $disc_price     = trim($_POST['discounted_price'] ?? '');
    $pickup_start   = trim($_POST['pickup_start']     ?? '');
    $pickup_end     = trim($_POST['pickup_end']       ?? '');

    $old = compact('title','description','category','quantity','original_price','disc_price','pickup_start','pickup_end');

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

    // Image upload (optional replacement)
    $image_filename = $listing['image']; // keep existing by default
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            $errors['image'] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        } elseif (!is_uploaded_file($_FILES['image']['tmp_name'])) {
            $errors['image'] = 'Image upload failed.';
        } else {
            $new_filename = uniqid('food_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $new_filename)) {
                $image_filename = $new_filename;
            } else {
                $errors['image'] = 'Could not save the uploaded image.';
            }
        }
    }

    if (empty($errors)) {
        $urgency = get_urgency_score($description, $pickup_end);

        $stmt2 = $pdo->prepare(
            'UPDATE food_listings SET
                title = ?, description = ?, category = ?,
                original_price = ?, discounted_price = ?,
                quantity = ?, pickup_start = ?, pickup_end = ?,
                image = ?, urgency_score = ?
             WHERE id = ? AND business_id = ?'
        );
        $stmt2->execute([
            $title, $description, $category,
            (float)$original_price, (float)$disc_price,
            (int)$quantity, $pickup_start, $pickup_end,
            $image_filename, $urgency,
            $id, $business_id,
        ]);

        header('Location: my_listings.php?updated=1');
        exit;
    }

    // Re-populate form with submitted data for re-render
    $listing = array_merge($listing, [
        'title'            => $title,
        'description'      => $description,
        'category'         => $category,
        'quantity'         => $quantity,
        'original_price'   => $original_price,
        'discounted_price' => $disc_price,
        'pickup_start'     => $pickup_start,
        'pickup_end'       => $pickup_end,
    ]);
}

// Format datetime for datetime-local input
$ps_fmt = date('Y-m-d\TH:i', strtotime($listing['pickup_start']));
$pe_fmt = date('Y-m-d\TH:i', strtotime($listing['pickup_end']));

$page_title  = 'Edit Listing';
$active_page = 'my_listings';
require_once '../includes/header.php';
?>

<main>
<div class="container py-4" style="max-width: 750px;">
    <h1 class="h3 fw-bold mb-1">Edit Listing</h1>
    <p class="text-muted mb-4">Urgency score will be recalculated when you save.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">Please fix the errors below before saving.</div>
    <?php endif; ?>

    <form method="post" action="edit_listing.php" enctype="multipart/form-data" id="edit-listing-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">

        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Title</label>
            <input type="text" id="title" name="title" maxlength="200" required
                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errors['title'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($listing['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="category" class="form-label fw-semibold">Category</label>
                <select id="category" name="category" required
                        class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>">
                    <?php foreach (['meals','bakery','produce','dairy','other'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($listing['category'] === $cat) ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="quantity" class="form-label fw-semibold">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" required
                       class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($listing['quantity'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['quantity'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['quantity'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="original_price" class="form-label fw-semibold">Original Price (LKR)</label>
                <input type="number" step="0.01" min="0" id="original_price" name="original_price" required
                       class="form-control <?= isset($errors['original_price']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($listing['original_price'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['original_price'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['original_price'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="discounted_price" class="form-label fw-semibold">Discounted Price (LKR)</label>
                <input type="number" step="0.01" min="0" id="discounted_price" name="discounted_price" required
                       class="form-control <?= isset($errors['discounted_price']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($listing['discounted_price'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['discounted_price'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['discounted_price'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="pickup_start" class="form-label fw-semibold">Pickup Start</label>
                <input type="datetime-local" id="pickup_start" name="pickup_start" required
                       class="form-control <?= isset($errors['pickup_start']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($ps_fmt, ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['pickup_start'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['pickup_start'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <label for="pickup_end" class="form-label fw-semibold">Pickup End</label>
                <input type="datetime-local" id="pickup_end" name="pickup_end" required
                       class="form-control <?= isset($errors['pickup_end']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($pe_fmt, ENT_QUOTES, 'UTF-8') ?>">
                <?php if (isset($errors['pickup_end'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['pickup_end'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="image" class="form-label fw-semibold">Replace Image <span class="text-muted fw-normal">(optional)</span></label>
            <?php if ($listing['image']): ?>
                <div class="mb-2">
                    <img src="../uploads/<?= htmlspecialchars($listing['image'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="Current image" class="img-thumbnail" style="max-height:120px;">
                    <div class="form-text">Current image. Upload a new file to replace it.</div>
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image"
                   class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                   accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['image'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" id="btn-save-listing" class="btn btn-success px-4 fw-semibold">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>
            <a href="my_listings.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

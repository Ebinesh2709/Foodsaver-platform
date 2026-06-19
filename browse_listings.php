<?php
session_start();
define('APP_RUNNING', true);
require_once 'includes/csrf_helper.php';
require_once 'config/db.php';
require_once 'includes/ai_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

// ── Natural language search ──────────────────────────────────
$search_query = trim($_GET['search'] ?? '');
$filters      = [];
$ai_used      = false;

if ($search_query !== '') {
    $filters = parse_natural_language_search($search_query);
    $ai_used = true;
}

// ── Build SQL based on filters ───────────────────────────────
$sql    = "SELECT fl.id, fl.title, fl.description, fl.quantity,
                  fl.pickup_end, fl.original_price, fl.discounted_price,
                  fl.image, fl.urgency_score, fl.category,
                  u.name AS business_name
           FROM food_listings fl
           JOIN users u ON fl.business_id = u.id
           WHERE fl.status = 'available'";

$params = [];

if (!empty($filters['category'])) {
    $sql     .= " AND fl.category = ?";
    $params[] = $filters['category'];
}
if (!empty($filters['min_quantity'])) {
    $sql     .= " AND fl.quantity >= ?";
    $params[] = (int)$filters['min_quantity'];
}
if (!empty($filters['urgency'])) {
    $sql     .= " AND fl.urgency_score = ?";
    $params[] = $filters['urgency'];
}
if (!empty($filters['keyword'])) {
    $sql     .= " AND (fl.title LIKE ? OR fl.description LIKE ?)";
    $params[] = '%' . $filters['keyword'] . '%';
    $params[] = '%' . $filters['keyword'] . '%';
}

$sql .= " ORDER BY 
          CASE fl.urgency_score 
            WHEN 'high'   THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low'    THEN 3 
            ELSE 4 
          END, fl.pickup_end ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Browse Food — FoodSaver</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Available Food Near You</h2>

  <!-- Natural language search box -->
  <form method="GET" class="mb-4">
    <div class="input-group">
      <input type="text" name="search" class="form-control form-control-lg"
             placeholder="Try: I need rice for 10 people before tonight..."
             value="<?= htmlspecialchars($search_query) ?>">
      <button class="btn btn-primary">Search</button>
      <?php if ($search_query): ?>
        <a href="browse_listings.php" class="btn btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </div>
    <small class="text-muted">Powered by AI — describe what you need in plain English</small>
  </form>

  <!-- Show what filters AI extracted, so the user understands what happened -->
  <?php if ($ai_used && !empty(array_filter($filters))): ?>
  <div class="alert alert-info">
    <strong>AI understood your search as:</strong>
    <?php if (!empty($filters['category'])): ?>
      Category: <span class="badge bg-primary"><?= htmlspecialchars($filters['category']) ?></span>
    <?php endif; ?>
    <?php if (!empty($filters['min_quantity'])): ?>
      Min quantity: <span class="badge bg-secondary"><?= htmlspecialchars($filters['min_quantity']) ?></span>
    <?php endif; ?>
    <?php if (!empty($filters['urgency'])): ?>
      Urgency: <span class="badge bg-warning"><?= htmlspecialchars($filters['urgency']) ?></span>
    <?php endif; ?>
    <?php if (!empty($filters['keyword'])): ?>
      Keyword: <span class="badge bg-dark"><?= htmlspecialchars($filters['keyword']) ?></span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Results -->
  <?php if (empty($listings)): ?>
    <div class="alert alert-warning">No listings found matching your search. Try different words.</div>
  <?php endif; ?>

  <div class="row">
  <?php foreach ($listings as $row): ?>
    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <?php if ($row['image']): ?>
          <img src="uploads/<?= htmlspecialchars($row['image']) ?>"
               class="card-img-top" style="height:180px;object-fit:cover">
        <?php endif; ?>
        <div class="card-body">
          <h5>
            <?= htmlspecialchars($row['title']) ?>
            <span class="badge bg-<?= $row['urgency_score']==='high' ? 'danger' : ($row['urgency_score']==='medium' ? 'warning' : 'secondary') ?>">
              <?= htmlspecialchars($row['urgency_score'] ?? 'low') ?>
            </span>
          </h5>
          <p class="text-muted small">
            <?= htmlspecialchars($row['category']) ?> ·
            <?= htmlspecialchars($row['business_name']) ?>
          </p>
          <p><?= htmlspecialchars($row['description']) ?></p>
          <p><strong>Qty:</strong> <?= htmlspecialchars($row['quantity']) ?></p>
          <?php if ($row['discounted_price']): ?>
            <p>
              <del class="text-muted">LKR <?= htmlspecialchars($row['original_price']) ?></del>
              <span class="text-success fw-bold">LKR <?= htmlspecialchars($row['discounted_price']) ?></span>
            </p>
          <?php endif; ?>
          <p><strong>Pickup by:</strong> <?= htmlspecialchars($row['pickup_end']) ?></p>
          <form method="POST" action="reserve_listing.php">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="listing_id" value="<?= $row['id'] ?>">
            <button class="btn btn-success w-100">Reserve</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>
</body>
</html>
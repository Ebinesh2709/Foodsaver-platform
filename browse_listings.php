<?php
session_start();

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';
require_once 'includes/urgency_fallback.php';
require_once 'includes/ai_helper.php';

$filters      = [];
$search_query = '';
$active_filters = [];

if (!empty($_GET['q'])) {
    $search_query   = trim($_GET['q']);
    $filters        = parse_natural_language_search($search_query);
    $active_filters = array_filter($filters, fn($v) => $v !== null);
}

// Build dynamic SQL
$conditions = ["fl.status = 'available'"];
$params     = [];

if (!empty($filters['category'])) {
    $conditions[] = 'fl.category = ?';
    $params[]     = $filters['category'];
}
if (!empty($filters['min_quantity'])) {
    $conditions[] = 'fl.quantity >= ?';
    $params[]     = (int)$filters['min_quantity'];
}
if (!empty($filters['urgency'])) {
    $conditions[] = 'fl.urgency_score = ?';
    $params[]     = $filters['urgency'];
}
if (!empty($filters['keyword'])) {
    $conditions[] = '(fl.title LIKE ? OR fl.description LIKE ?)';
    $kw           = '%' . $filters['keyword'] . '%';
    $params[]     = $kw;
    $params[]     = $kw;
}

$where_clause = implode(' AND ', $conditions);

$sql = "SELECT fl.*, b.business_name, b.area
        FROM food_listings fl
        JOIN businesses b ON fl.business_id = b.id
        WHERE {$where_clause}
        ORDER BY FIELD(fl.urgency_score, 'high', 'medium', 'low'), fl.pickup_end ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

$page_title  = 'Browse Listings';
$active_page = 'browse';
require_once 'includes/header.php';
?>

<main>
<div class="container py-4">
    <h1 class="h3 fw-bold mb-3">Browse Food Listings</h1>

    <!-- AI Search Form -->
    <form method="get" action="browse_listings.php" class="mb-4" id="search-form">
        <div class="input-group input-group-lg shadow-sm">
            <span class="input-group-text bg-white border-end-0">🔍</span>
            <input type="text" id="q" name="q" class="form-control border-start-0"
                   placeholder="e.g. rice meals for tonight, cheap bakery items..."
                   value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" id="btn-search" class="btn btn-success px-4">Search</button>
            <?php if ($search_query): ?>
                <a href="browse_listings.php" id="btn-clear-search" class="btn btn-outline-secondary px-3">Clear</a>
            <?php endif; ?>
        </div>
        <div class="form-text ms-1">Use natural language — our AI extracts filters automatically.</div>
    </form>

    <!-- Active Filters -->
    <?php if (!empty($active_filters)): ?>
        <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small fw-semibold">AI Filters:</span>
            <?php if (!empty($filters['category'])): ?>
                <span class="badge bg-info text-dark">Category: <?= htmlspecialchars(ucfirst($filters['category']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($filters['min_quantity'])): ?>
                <span class="badge bg-info text-dark">Min Qty: <?= (int)$filters['min_quantity'] ?></span>
            <?php endif; ?>
            <?php if (!empty($filters['urgency'])): ?>
                <span class="badge bg-info text-dark">Urgency: <?= htmlspecialchars(ucfirst($filters['urgency']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($filters['keyword'])): ?>
                <span class="badge bg-info text-dark">Keyword: "<?= htmlspecialchars($filters['keyword'], ENT_QUOTES, 'UTF-8') ?>"</span>
            <?php endif; ?>
        </div>
        <p class="text-muted small mb-3"><?= count($listings) ?> result<?= count($listings) !== 1 ? 's' : '' ?> found.</p>
    <?php endif; ?>

    <!-- Listings Grid -->
    <?php if (empty($listings)): ?>
        <div class="text-center py-5">
            <div class="display-1">🍽️</div>
            <h2 class="h5 mt-3">No listings found</h2>
            <p class="text-muted">
                <?= $search_query ? 'Try a different search or browse all listings.' : 'No food listings are currently available. Check back soon!' ?>
            </p>
            <?php if ($search_query): ?>
                <a href="browse_listings.php" class="btn btn-outline-success">Browse All</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($listings as $listing): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 urgency-<?= htmlspecialchars($listing['urgency_score'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($listing['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($listing['image'], ENT_QUOTES, 'UTF-8') ?>"
                             class="card-img-top" alt="<?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?>"
                             style="height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="text-center bg-light d-flex align-items-center justify-content-center" style="height:180px; font-size:4rem;">🍱</div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <?= get_urgency_badge_html($listing['urgency_score']) ?>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars(ucfirst($listing['category']), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>

                        <h2 class="h6 fw-bold mb-1"><?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-shop me-1"></i><?= htmlspecialchars($listing['business_name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($listing['area']): ?>
                                · <?= htmlspecialchars($listing['area'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </p>
                        <p class="small text-muted mb-2">
                            <?= htmlspecialchars(mb_strimwidth($listing['description'], 0, 100, '…'), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-bold text-success fs-5">LKR <?= htmlspecialchars(number_format((float)$listing['discounted_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="text-muted text-decoration-line-through ms-1 small">LKR <?= htmlspecialchars(number_format((float)$listing['original_price'], 2), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <span class="badge bg-light text-dark border">Qty: <?= (int)$listing['quantity'] ?></span>
                        </div>

                        <p class="small text-muted mb-3">
                            <i class="bi bi-clock me-1"></i>Pickup by: <strong><?= htmlspecialchars(date('d M Y, H:i', strtotime($listing['pickup_end'])), ENT_QUOTES, 'UTF-8') ?></strong>
                        </p>

                        <div class="mt-auto">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                                <form method="post" action="reserve_listing.php" id="reserve-form-<?= (int)$listing['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                                    <button type="submit" id="btn-reserve-<?= (int)$listing['id'] ?>" class="btn btn-success w-100">
                                        <i class="bi bi-bag-plus me-1"></i>Reserve Now
                                    </button>
                                </form>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="auth/login.php" class="btn btn-outline-success w-100">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Login to Reserve
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>Reserve</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</main>

<?php require_once 'includes/footer.php'; ?>

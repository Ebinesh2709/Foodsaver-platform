<?php
session_start();

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';
require_once 'includes/urgency_fallback.php';
require_once 'includes/ai_helper.php';

$filters        = [];
$search_query   = '';
$ai_response    = '';
$is_search      = false;

if (!empty($_GET['q'])) {
    $search_query = substr(trim($_GET['q']), 0, 300);
    $is_search    = true;
    $filters      = parse_natural_language_search($search_query);
}

// Build dynamic WHERE
$where_parts = ["fl.status = 'available'"];
$bind_values = [];

if (!empty($filters['category'])) {
    $where_parts[] = 'fl.category = ?';
    $bind_values[] = $filters['category'];
}
if (!empty($filters['min_quantity'])) {
    $where_parts[] = 'fl.quantity >= ?';
    $bind_values[] = (int)$filters['min_quantity'];
}
if (!empty($filters['urgency'])) {
    $where_parts[] = 'fl.urgency_score = ?';
    $bind_values[] = $filters['urgency'];
}

// Synonym-based keyword OR search
$search_terms = array_filter(array_merge(
    [$filters['keyword'] ?? null],
    $filters['synonyms'] ?? []
));
$like_clauses = [];
foreach ($search_terms as $term) {
    $like_clauses[] = '(fl.title LIKE ? OR fl.description LIKE ?)';
    $bind_values[]  = '%' . $term . '%';
    $bind_values[]  = '%' . $term . '%';
}
if (!empty($like_clauses)) {
    $where_parts[] = '(' . implode(' OR ', $like_clauses) . ')';
}

$where_clause = implode(' AND ', $where_parts);

$sql = "SELECT fl.*, b.business_name, b.area
        FROM food_listings fl
        JOIN businesses b ON fl.business_id = b.id
        WHERE {$where_clause}
        ORDER BY FIELD(fl.urgency_score, 'high', 'medium', 'low'), fl.pickup_end ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($bind_values);
$listings = $stmt->fetchAll();

// Generate AI conversational response for search
if ($is_search) {
    $ai_response = generate_search_response(
        $filters['intent_summary'] ?? $search_query,
        count($listings),
        (int)($filters['min_quantity'] ?? 0)
    );
}

// If search returned 0 results, also fetch all available for fallback display
$all_listings = [];
if ($is_search && empty($listings)) {
    $stmt2 = $pdo->prepare(
        "SELECT fl.*, b.business_name, b.area
         FROM food_listings fl
         JOIN businesses b ON fl.business_id = b.id
         WHERE fl.status = 'available'
         ORDER BY FIELD(fl.urgency_score, 'high', 'medium', 'low'), fl.pickup_end ASC"
    );
    $stmt2->execute();
    $all_listings = $stmt2->fetchAll();
}

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
                   placeholder="e.g. I need 15 fried rice, cheap bakery items tonight..."
                   value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" id="btn-search" class="btn btn-success px-4">Search</button>
            <?php if ($search_query): ?>
                <a href="browse_listings.php" id="btn-clear-search" class="btn btn-outline-secondary px-3">Clear</a>
            <?php endif; ?>
        </div>
        <div class="form-text ms-1">Use natural language — our AI extracts filters automatically.</div>
    </form>

    <!-- Error Alerts -->
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'unavailable'): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>Sorry, that item was just reserved by someone else.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-x-circle me-2"></i>Reservation failed. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- AI Response -->
    <?php if ($is_search && $ai_response): ?>
        <div class="alert alert-info mb-3">
            <i class="bi bi-robot me-2"></i><?= htmlspecialchars($ai_response, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Active Filter Badges -->
    <?php if ($is_search): ?>
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
            <?php if (!empty($filters['synonyms'])): ?>
                <span class="badge bg-light text-dark border">Also searched: <?= htmlspecialchars(implode(', ', $filters['synonyms']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Results -->
    <?php
    $display_listings  = $listings;
    $show_fallback_all = $is_search && empty($listings);
    ?>

    <?php if (empty($display_listings) && !$show_fallback_all): ?>
        <div class="text-center py-5">
            <div class="display-1">🍽️</div>
            <h2 class="h5 mt-3">No listings found</h2>
            <p class="text-muted">No food listings are currently available. Check back soon!</p>
        </div>
    <?php else: ?>
        <?php if (!empty($display_listings)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
            <?php foreach ($display_listings as $listing): ?>
                <?php include __DIR__ . '/includes/_listing_card.php'; ?>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_fallback_all && !empty($all_listings)): ?>
            <h2 class="h5 fw-bold mb-3 mt-2">All available listings</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($all_listings as $listing): ?>
                <?php include __DIR__ . '/includes/_listing_card.php'; ?>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>

<?php require_once 'includes/footer.php'; ?>

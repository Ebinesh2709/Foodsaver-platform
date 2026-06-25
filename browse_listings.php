<?php
session_start();

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';
require_once 'includes/urgency_fallback.php';
require_once 'includes/ai_helper.php';

$filters      = [];
$search_query = '';
$ai_response  = '';
$is_search    = false;

if (!empty($_GET['q'])) {
    $search_query = substr(trim($_GET['q']), 0, 300);
    $is_search    = true;
    $filters      = parse_natural_language_search($search_query);
}

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

if ($is_search) {
    $ai_response = generate_search_response(
        $filters['intent_summary'] ?? $search_query,
        count($listings),
        (int)($filters['min_quantity'] ?? 0)
    );
}

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
$css_prefix  = '';
require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="fs-page-header">
    <div class="container">
        <h1><i class="bi bi-grid me-2"></i>Browse Food Listings</h1>
        <p>Fresh surplus food from local businesses — reserve before it's gone</p>
    </div>
</div>

<div class="container pb-5">

    <!-- Search Bar -->
    <div class="fs-search-bar mb-4">
        <form method="get" action="browse_listings.php" id="search-form">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-stars"></i></span>
                <input type="text" id="q" name="q" class="form-control"
                       placeholder="e.g. I need 15 fried rice, cheap bakery items tonight..."
                       value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" id="btn-search" class="btn-search px-4">Search</button>
                <?php if ($search_query): ?>
                    <a href="browse_listings.php" id="btn-clear-search" class="btn btn-fs-outline px-3" style="border-radius:0 var(--fs-radius-sm) var(--fs-radius-sm) 0;">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="form-text ms-1 mt-1">
                <i class="bi bi-robot me-1"></i>Powered by Gemini AI — understands natural language
            </div>
        </form>
    </div>

    <!-- Error Alerts -->
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'unavailable'): ?>
            <div class="fs-alert-warning mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Sorry, that item was just reserved by someone else.</div>
        <?php elseif ($_GET['error'] === 'failed'): ?>
            <div class="fs-alert-danger mb-3"><i class="bi bi-x-circle me-2"></i>Reservation failed. Please try again.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- AI Response -->
    <?php if ($is_search && $ai_response): ?>
        <div class="fs-alert-ai mb-3">
            <span class="ai-icon">🤖</span>
            <span><?= htmlspecialchars($ai_response, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Active Filter Chips -->
    <?php if ($is_search && (!empty($filters['category']) || !empty($filters['min_quantity']) || !empty($filters['urgency']) || !empty($filters['keyword']))): ?>
        <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
            <span style="font-size:0.78rem; font-weight:700; color:var(--fs-text-muted);">AI FILTERS</span>
            <?php if (!empty($filters['category'])): ?>
                <span class="filter-chip"><i class="bi bi-tag"></i><?= htmlspecialchars(ucfirst($filters['category']), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($filters['min_quantity'])): ?>
                <span class="filter-chip"><i class="bi bi-123"></i>Min Qty: <?= (int)$filters['min_quantity'] ?></span>
            <?php endif; ?>
            <?php if (!empty($filters['urgency'])): ?>
                <span class="filter-chip"><i class="bi bi-clock"></i><?= htmlspecialchars(ucfirst($filters['urgency']), ENT_QUOTES, 'UTF-8') ?> Urgency</span>
            <?php endif; ?>
            <?php if (!empty($filters['keyword'])): ?>
                <span class="filter-chip"><i class="bi bi-search"></i>"<?= htmlspecialchars($filters['keyword'], ENT_QUOTES, 'UTF-8') ?>"</span>
            <?php endif; ?>
            <?php if (!empty($filters['synonyms'])): ?>
                <span class="filter-chip" style="background:rgba(0,0,0,0.04); color:var(--fs-text-muted); border-color:var(--fs-border);">
                    also: <?= htmlspecialchars(implode(', ', $filters['synonyms']), ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Results -->
    <?php $show_fallback_all = $is_search && empty($listings); ?>

    <?php if (empty($listings) && !$show_fallback_all): ?>
        <div class="fs-empty">
            <span class="empty-icon">🍽️</span>
            <h2>No listings available</h2>
            <p>No food listings are currently available. Check back soon — businesses post new items daily!</p>
        </div>
    <?php else: ?>
        <?php if (!empty($listings)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                <?php foreach ($listings as $listing): ?>
                    <?php include __DIR__ . '/includes/_listing_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_fallback_all): ?>
            <div class="fs-alert-warning mb-4">
                <i class="bi bi-info-circle me-2"></i>No exact matches found for your search. Showing all available listings below.
            </div>
            <?php if (!empty($all_listings)): ?>
                <h2 class="h5 fw-bold mb-3">All Available Listings</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($all_listings as $listing): ?>
                        <?php include __DIR__ . '/includes/_listing_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="fs-empty">
                    <span class="empty-icon">🍽️</span>
                    <h2>No listings available right now</h2>
                    <p>Check back soon — businesses post new items daily!</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>

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

<!-- Premium Search Hero (Playful Style) -->
<div class="fs-search-hero mx-auto mt-4 mb-5" style="max-width: 1200px; padding: 2rem;">
    <div class="hero-banner" style="background: #bcf37a; border-radius: 40px; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 0 40px; min-height: 270px; overflow: visible;">
        <div class="hero-text" style="flex: 1; padding: 40px 0;">
            <h2 style="font-size: 2.4rem; color: #1e293b; margin: 0; line-height: 1.2; font-weight: 800; max-width: 500px;">Surprises Await The Bold!</h2>
            <p style="color: #334155; font-weight: 600; font-size: 1.15rem; margin-top: 12px;">Discover fresh surplus food from local businesses at a fraction of the cost.</p>
            
            <form method="get" action="browse_listings.php" id="search-form" class="m-0 mt-4" style="max-width: 450px;">
                <div class="input-group" style="background: white; border-radius: 12px; padding: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <input type="text" id="q" name="q" class="form-control border-0 bg-transparent shadow-none px-3"
                           placeholder="e.g. I need 5 fried rice..."
                           value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>"
                           style="font-size: 1rem; height: 45px; font-family: 'Fredoka', sans-serif; color: #333;">
                    <button type="submit" id="btn-search" class="btn px-4" style="background: #ffbe0b; color: white; border-radius: 10px; font-weight: 600;">Search</button>
                    <?php if ($search_query): ?>
                        <a href="browse_listings.php" id="btn-clear-search" class="btn btn-light px-3 ms-2 d-flex align-items-center justify-content-center" style="border-radius: 10px;">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="form-text mt-2" style="font-size: 0.85rem; color: #2d5a27; font-weight: 600;">
                    <i class="bi bi-robot me-1"></i>Powered by Groq AI
                </div>
            </form>
        </div>
        
        <div class="hero-banner-image d-none d-lg-flex" style="flex: 1; height: 350px; align-items: flex-end; justify-content: flex-end; margin-bottom: -40px;">
            <img src="assets/img/mascot_hero.png" alt="Premium Mascot" style="height: 100%; object-fit: contain; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));">
        </div>
    </div>
</div>

<div class="container premium-section" style="max-width: 1200px;">

    <!-- Cuisines Row -->
    <h3 class="fw-bold mb-3" style="font-size: 1.2rem; color: #333;">Cuisines & Categories</h3>
    <div class="cuisine-row mb-5">
        <a href="browse_listings.php?q=meals" class="filter-chip">
            <i class="bi bi-egg-fried"></i>
            Meals
        </a>
        <a href="browse_listings.php?q=bakery" class="filter-chip">
            <i class="bi bi-baguette"></i>
            Bakery
        </a>
        <a href="browse_listings.php?q=groceries" class="filter-chip">
            <i class="bi bi-basket"></i>
            Groceries
        </a>
        <a href="browse_listings.php?q=vegetarian" class="filter-chip">
            <i class="bi bi-flower1"></i>
            Vegetarian
        </a>
        <a href="browse_listings.php?q=drinks" class="filter-chip">
            <i class="bi bi-cup-straw"></i>
            Drinks
        </a>
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
        <div class="fs-alert-ai mb-4" style="background:#fff; border-radius:24px; border:none; box-shadow:0 8px 24px rgba(0,0,0,0.05); padding:1.5rem;">
            <span class="ai-icon" style="font-size:1.5rem;">🤖</span>
            <span style="font-weight:600; color:#333; font-size:1.05rem;"><?= htmlspecialchars($ai_response, ENT_QUOTES, 'UTF-8') ?></span>
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

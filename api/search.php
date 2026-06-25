<?php
session_start();
define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/urgency_fallback.php';
require_once '../includes/ai_helper.php';
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$query = substr($query, 0, 300);

if (empty($query)) {
    echo json_encode(['error' => 'No query provided']);
    exit;
}

$filters = parse_natural_language_search($query);

// Build same dynamic WHERE as browse_listings.php
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

$sql = "SELECT fl.id, fl.title, fl.category, fl.urgency_score,
               fl.discounted_price, fl.original_price, fl.quantity,
               fl.pickup_end, fl.image, fl.ai_summary,
               b.business_name, b.area
        FROM food_listings fl
        JOIN businesses b ON fl.business_id = b.id
        WHERE {$where_clause}
        ORDER BY FIELD(fl.urgency_score, 'high', 'medium', 'low'), fl.pickup_end ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($bind_values);
$rows = $stmt->fetchAll();

echo json_encode([
    'filters' => $filters,
    'results' => $rows,
    'count'   => count($rows),
]);

<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header('Location: ../auth/login.php');
    exit;
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_listings.php');
    exit;
}

define('APP_RUNNING', true);
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$listing_id = (int)($_POST['listing_id'] ?? 0);

// Get business_id
$stmt = $pdo->prepare('SELECT id FROM businesses WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$biz_row = $stmt->fetch();

if (!$biz_row) {
    header('Location: my_listings.php');
    exit;
}
$business_id = $biz_row['id'];

// Delete only if the listing belongs to this business (double condition prevents cross-business deletion)
$stmt2 = $pdo->prepare('DELETE FROM food_listings WHERE id = ? AND business_id = ?');
$stmt2->execute([$listing_id, $business_id]);

header('Location: my_listings.php');
exit;

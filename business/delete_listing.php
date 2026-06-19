<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$stmt_biz = $pdo->prepare("SELECT id FROM businesses WHERE user_id = ?");
$stmt_biz->execute([$_SESSION['user_id']]);
$business = $stmt_biz->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    die("No business profile found. Please complete your business registration first.");
}
$business_id = $business['id'];
$listing_id = $_GET['id'] ?? null;

if ($listing_id) {
    $stmt = $pdo->prepare("DELETE FROM food_listings WHERE id = ? AND business_id = ?");
    $stmt->execute([$listing_id, $business_id]);
}
header("Location: my_listings.php");
exit;
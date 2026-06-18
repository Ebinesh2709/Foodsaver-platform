<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'business') {
    header("Location: ../login.php"); exit;
}
$business_id = $_SESSION['user_id'];
$listing_id = $_GET['id'] ?? null;

if ($listing_id) {
    $stmt = $conn->prepare("DELETE FROM food_listings WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $listing_id, $business_id);
    $stmt->execute();
}
header("Location: my_listings.php");
exit;
<?php
session_start();

// Role guard — customers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: auth/login.php');
    exit;
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse_listings.php');
    exit;
}

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$listing_id = (int)($_POST['listing_id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Lock the listing row and check availability
    $stmt = $pdo->prepare("SELECT * FROM food_listings WHERE id = ? AND status = 'available' FOR UPDATE");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch();

    if (!$listing) {
        $pdo->rollBack();
        header('Location: browse_listings.php?error=unavailable');
        exit;
    }

    // Insert reservation
    $stmt2 = $pdo->prepare("INSERT INTO reservations (listing_id, user_id, status) VALUES (?, ?, 'pending')");
    $stmt2->execute([$listing_id, $user_id]);

    // Mark listing as reserved
    $stmt3 = $pdo->prepare("UPDATE food_listings SET status = 'reserved' WHERE id = ?");
    $stmt3->execute([$listing_id]);

    $pdo->commit();

    header('Location: my_reservations.php?reserved=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: browse_listings.php?error=failed');
    exit;
}

<?php
session_start();

// Role guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: auth/login.php');
    exit;
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_reservations.php');
    exit;
}

define('APP_RUNNING', true);
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';

verify_csrf_token($_POST['csrf_token'] ?? '');

$reservation_id = (int)($_POST['reservation_id'] ?? 0);
$user_id        = (int)$_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Verify this reservation belongs to the current user and is still pending
    $stmt = $pdo->prepare(
        "SELECT * FROM reservations WHERE id = ? AND user_id = ? AND status = 'pending'"
    );
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        $pdo->rollBack();
        header('Location: my_reservations.php');
        exit;
    }

    $listing_id = (int)$reservation['listing_id'];

    // Cancel the reservation
    $stmt2 = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
    $stmt2->execute([$reservation_id]);

    // Make the listing available again
    $stmt3 = $pdo->prepare("UPDATE food_listings SET status = 'available' WHERE id = ?");
    $stmt3->execute([$listing_id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
}

header('Location: my_reservations.php');
exit;

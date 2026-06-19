<?php
session_start();
define('APP_RUNNING', true);
require_once 'includes/csrf_helper.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $listing_id = $_POST['listing_id'] ?? null;
    if ($listing_id) {
        $check = $pdo->prepare("SELECT status FROM food_listings WHERE id = ?");
        $check->execute([$listing_id]);
        $listing = $check->fetch(PDO::FETCH_ASSOC);

        if ($listing && $listing['status'] === 'available') {
            $pdo->beginTransaction();
            try {
                $insert = $pdo->prepare("INSERT INTO reservations (listing_id, user_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
                $insert->execute([$listing_id, $user_id]);
                $update = $pdo->prepare("UPDATE food_listings SET status = 'reserved' WHERE id = ?");
                $update->execute([$listing_id]);
                $pdo->commit();
            } catch (Exception $e) { $pdo->rollBack(); }
        }
    }
}
header("Location: my_reservations.php");
exit;
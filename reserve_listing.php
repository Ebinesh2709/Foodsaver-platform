<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = $_POST['listing_id'] ?? null;
    if ($listing_id) {
        $check = $conn->prepare("SELECT status FROM food_listings WHERE id = ?");
        $check->bind_param("i", $listing_id);
        $check->execute();
        $listing = $check->get_result()->fetch_assoc();

        if ($listing && $listing['status'] === 'available') {
            $conn->begin_transaction();
            try {
                $insert = $conn->prepare("INSERT INTO reservations (listing_id, user_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
                $insert->bind_param("ii", $listing_id, $user_id);
                $insert->execute();

                $update = $conn->prepare("UPDATE food_listings SET status = 'reserved' WHERE id = ?");
                $update->bind_param("i", $listing_id);
                $update->execute();
                $conn->commit();
            } catch (Exception $e) { $conn->rollback(); }
        }
    }
}
header("Location: my_reservations.php");
exit;
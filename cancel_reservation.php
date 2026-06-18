<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$reservation_id = $_GET['id'] ?? null;

if ($reservation_id) {
    $stmt = $conn->prepare("SELECT listing_id FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reservation_id, $user_id);
    $stmt->execute();
    $reservation = $stmt->get_result()->fetch_assoc();

    if ($reservation) {
        $conn->begin_transaction();
        try {
            $c = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $c->bind_param("ii", $reservation_id, $user_id);
            $c->execute();
            $r = $conn->prepare("UPDATE food_listings SET status = 'available' WHERE id = ?");
            $r->bind_param("i", $reservation['listing_id']);
            $r->execute();
            $conn->commit();
        } catch (Exception $e) { $conn->rollback(); }
    }
}
header("Location: my_reservations.php");
exit;
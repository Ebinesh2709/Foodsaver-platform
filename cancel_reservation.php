<?php
session_start();
define('APP_RUNNING', true);
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$reservation_id = $_GET['id'] ?? null;

if ($reservation_id) {
    $stmt = $pdo->prepare("SELECT listing_id FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        $pdo->beginTransaction();
        try {
            $c = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $c->execute([$reservation_id, $user_id]);
            $r = $pdo->prepare("UPDATE food_listings SET status = 'available' WHERE id = ?");
            $r->execute([$reservation['listing_id']]);
            $pdo->commit();
        } catch (Exception $e) { $pdo->rollBack(); }
    }
}
header("Location: my_reservations.php");
exit;
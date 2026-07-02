<?php
session_start();
define('APP_RUNNING', true);
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$phone = trim($_POST['phone'] ?? '');
$code = trim($_POST['code'] ?? '');

if (!$phone || !$code) {
    echo json_encode(['success' => false, 'error' => 'Phone and OTP code are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM otp_codes WHERE phone = ? AND code = ? AND expires_at >= NOW() ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$phone, $code]);
$valid_otp = $stmt->fetch();

if (!$valid_otp) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired code.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET phone_verified = 1 WHERE phone = ?");
$stmt->execute([$phone]);

$stmt = $pdo->prepare("DELETE FROM otp_codes WHERE id = ?");
$stmt->execute([$valid_otp['id']]);

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch();

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['role']    = $user['role'];

$redirect = '../browse_listings.php';
if ($user['role'] === 'business') {
    $redirect = '../business/dashboard.php';
} elseif ($user['role'] === 'admin') {
    $redirect = '../admin/dashboard.php';
}

echo json_encode(['success' => true, 'redirect' => $redirect]);

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

if (!$phone) {
    echo json_encode(['success' => false, 'error' => 'Phone number is required.']);
    exit;
}

// Check if user exists with this phone
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'No account found with this phone number.']);
    exit;
}

// Generate 6 digit OTP
$code = sprintf("%06d", mt_rand(1, 999999));
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$stmt = $pdo->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$phone, $code, $expires_at]);

// Simulate success
echo json_encode([
    'success' => true, 
    'message' => 'OTP sent successfully',
    'simulated_code' => $code
]);

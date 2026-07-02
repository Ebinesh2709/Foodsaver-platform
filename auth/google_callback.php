<?php
session_start();
define('APP_RUNNING', true);
require_once '../config/db.php';

if (isset($_GET['mock_login'])) {
    $google_id = 'mock_google_id_123456';
    $email = 'googleuser@example.com';
    $name = 'Google User';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->execute([$google_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $stmt->execute([$google_id, $user['id']]);
        } else {
            $role = 'customer';
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, google_id) VALUES (?, ?, NULL, ?, ?)');
            $stmt->execute([$name, $email, $role, $google_id]);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ?");
            $stmt->execute([$google_id]);
            $user = $stmt->fetch();
        }
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['role']    = $user['role'];

    if ($user['role'] === 'business') {
        header('Location: ../business/dashboard.php');
    } elseif ($user['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../browse_listings.php');
    }
    exit;
}

echo "Invalid OAuth Request";

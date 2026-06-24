<?php
defined('APP_RUNNING') or die('Direct access not permitted');

// Copy this file to config/db.php and fill in your values
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

$host   = 'localhost';
$dbname = 'foodsaver_db';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
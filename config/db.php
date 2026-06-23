<?php
defined('APP_RUNNING') or die('Direct access not permitted');

define('GROQ_API_KEY', 'your-groq-api-key-here');

$host = 'localhost';
$dbname = 'foodsaver_db';
$user = 'root';
$pass = '';

if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    $host   = 'YOUR_PROD_HOST';
    $dbname = 'YOUR_PROD_DBNAME';
    $user   = 'YOUR_PROD_USER';
    $pass   = 'YOUR_PROD_PASS';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
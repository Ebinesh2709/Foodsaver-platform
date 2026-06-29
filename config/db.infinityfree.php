<?php
defined('APP_RUNNING') or die('Direct access not permitted');

define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

$host   = 'sql202.infinityfree.com';
$dbname = 'if0_42267378_foodsaver';
$user   = 'if0_42267378';
$pass   = 'Eb10723497723';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed. Please check your hosting MySQL credentials.');
}

<?php
define('APP_RUNNING', true);
$_SERVER['HTTP_HOST'] = 'localhost';
require_once '../config/db.php';

$api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode(GEMINI_API_KEY);

$payload = json_encode([
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => 'hello']]]
    ]
]);

$ch = curl_init($api_url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

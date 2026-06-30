<?php
/**
 * FoodSaver AI Chatbot Endpoint
 * POST /api/chatbot.php
 * Accepts JSON: { "message": "...", "history": [...] }
 * Returns JSON: { "reply": "..." } or { "error": "..." }
 *
 * Restricted to FoodSaver platform topics only.
 */

define('APP_RUNNING', true);
session_start();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow GET or POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// Rate limiting via session (max 30 messages per session)
if (!isset($_SESSION['chatbot_count'])) {
    $_SESSION['chatbot_count'] = 0;
}
if ($_SESSION['chatbot_count'] >= 30) {
    http_response_code(429);
    echo json_encode(['error' => 'You have reached the message limit for this session. Please refresh the page to continue.']);
    exit;
}

// Parse input
if (isset($_GET['payload'])) {
    $input = json_decode(base64_decode($_GET['payload']), true);
} else {
    $input = [];
}

if (!is_array($input) || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$user_message = trim((string)($input['message'] ?? ''));
$history      = is_array($input['history'] ?? null) ? $input['history'] : [];

if (strlen($user_message) > 500) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long. Please keep it under 500 characters.']);
    exit;
}
if (empty($user_message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}

// TEMPORARY DIAGNOSTIC RESPONSE
echo json_encode(['reply' => 'Diagnostic mode: The API endpoint was successfully reached and returned JSON!']);
exit;

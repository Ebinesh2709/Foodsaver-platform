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

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

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

// Load API key
require_once '../config/db.php';

// ── System Prompt ──────────────────────────────────────────────────────────
$system_prompt = <<<SYSTEM
You are FoodSaver Assistant — a helpful, friendly AI chatbot exclusively for the FoodSaver platform, a food waste reduction web application serving Sri Lanka.

== WHAT YOU CAN HELP WITH ==
You may ONLY answer questions that are directly related to the FoodSaver platform and its core features:
1. How the platform works (food redistribution, connecting businesses with customers).
2. Food listings: categories (meals, bakery, produce, dairy, other), urgency scores (high/medium/low), prices, pickup windows, how to browse.
3. Reservations: how to reserve a listing, how to cancel, reservation statuses (pending, confirmed, collected, cancelled).
4. User roles: customer (browse & reserve), business (post listings, manage reservations), admin (oversight).
5. Registration and login: how to sign up as a customer or business.
6. Searching for food: the AI-powered natural language search feature.
7. Pickup: how pickup windows work, what urgency scores mean, where to pick up food.
8. Food waste and SDG 12 context: general awareness of how the platform fights food waste.
9. General troubleshooting within the app (e.g., "why can't I reserve?", "what does 'confirmed' mean?").

== STRICT RESTRICTIONS ==
- Do NOT answer questions unrelated to FoodSaver. This includes: general cooking advice, recipes, nutrition information, politics, entertainment, coding help, math, weather, news, history, or any other off-topic subject.
- Do NOT make up specific details or hallucinate facts. You ONLY have access to the live food data provided below.
- Do NOT speculate about features that are not part of the platform.
- If the user asks something off-topic, politely decline and redirect them to what you CAN help with.
- Be honest if you don't know the answer. Never hallucinate facts.
- Keep responses concise, friendly, and helpful. Use simple English suitable for a Sri Lankan audience.
- Do not use excessive markdown. Use short paragraphs or short bullet lists only when genuinely helpful.
- Never pretend to be a human. Always be transparent that you are an AI assistant for FoodSaver.

== PLATFORM FACTS ==
- Platform name: FoodSaver
- Location focus: Sri Lanka
- Mission: Reduce food waste by connecting food businesses with local customers.
- SDG alignment: UN Sustainable Development Goal 12 — Responsible Consumption and Production.
- Food categories available: Meals, Bakery, Produce, Dairy, Other.
- Urgency levels: High (must collect soon, under 12 hours), Medium (12–48 hours), Low (over 48 hours).
- Reservation statuses: Pending → Confirmed → Collected. Can be Cancelled if still Pending.
- Customers must be registered and logged in to reserve food.
- Businesses post listings with a pickup time window and discounted price.
- All food listings are available at a discounted price compared to original price.
- You cannot make reservations through this chat — direct users to the Browse page.

== TONE ==
Warm, helpful, and concise. You care about reducing food waste.
SYSTEM;

// Fetch live available food listings from the database to give the AI real context
try {
    $stmt = $pdo->query("
        SELECT fl.title, fl.category, fl.quantity, fl.discounted_price, fl.pickup_end, b.business_name 
        FROM food_listings fl
        JOIN businesses b ON fl.business_id = b.id
        WHERE fl.status = 'available'
        ORDER BY fl.created_at DESC
        LIMIT 20
    ");
    $live_listings = $stmt->fetchAll();
    
    if (count($live_listings) > 0) {
        $system_prompt .= "\n\n== CURRENT LIVE FOOD LISTINGS ==\n";
        $system_prompt .= "Here is the real-time data from the database. Use this data to answer questions about available food right now:\n";
        foreach ($live_listings as $item) {
            $system_prompt .= "- {$item['title']} ({$item['category']}, Qty: {$item['quantity']}) at {$item['business_name']} for LKR {$item['discounted_price']}. Pickup ends: {$item['pickup_end']}\n";
        }
    } else {
        $system_prompt .= "\n\n== CURRENT LIVE FOOD LISTINGS ==\n";
        $system_prompt .= "There are currently NO active food listings available right now. Let the user know they should check back later.\n";
    }
} catch (Exception $e) {
    // Ignore db fetch errors for the chatbot and just proceed without live data
}

// ── Build conversation for Groq multi-turn format ────────────────────────
$contents = [
    ['role' => 'system', 'content' => $system_prompt]
];

if (!empty($history) && is_array($history)) {
    $history = array_slice($history, -16);
    foreach ($history as $turn) {
        if (!isset($turn['role'], $turn['text'])) continue;
        $role = $turn['role'] === 'model' ? 'assistant' : 'user';
        $text = substr(strip_tags((string)$turn['text']), 0, 600);
        if (empty($text)) continue;
        $contents[] = [
            'role'    => $role,
            'content' => $text
        ];
    }
}

// Add current user message
$contents[] = [
    'role'    => 'user',
    'content' => $user_message
];

// ── Call Groq API ──────────────────────────────────────────────────────────
$api_url = 'https://api.groq.com/openai/v1/chat/completions';

$payload = json_encode([
    'model'       => 'llama-3.1-8b-instant',
    'messages'    => $contents,
    'temperature' => 0.3,
    'max_tokens'  => 400
]);

$ch = curl_init($api_url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$curl_err  = curl_errno($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_err || $http_code !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'I\'m having trouble connecting right now. Please try again in a moment.']);
    exit;
}

$body = json_decode($response, true);
$reply = trim($body['choices'][0]['message']['content'] ?? '');

if (empty($reply)) {
    http_response_code(502);
    echo json_encode(['error' => 'I could not generate a response. Please try rephrasing your question.']);
    exit;
}

// Increment session counter on success
$_SESSION['chatbot_count']++;

echo json_encode(['reply' => $reply]);

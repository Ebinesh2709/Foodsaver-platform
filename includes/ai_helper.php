<?php

// ============================================================
// URGENCY SCORING
// Called when a business posts a listing
// Returns 'high', 'medium', or 'low'
// ============================================================
function get_urgency_score($description, $pickup_end) {
    $hours_until_pickup = (strtotime($pickup_end) - time()) / 3600;

    // Try AI first
    $ai_result = call_ai_for_urgency($description, $hours_until_pickup);
    if ($ai_result !== null) return $ai_result;

    // Fallback rule if AI fails
    if ($hours_until_pickup <= 12) return 'high';
    if ($hours_until_pickup <= 48) return 'medium';
    return 'low';
}

function call_ai_for_urgency($description, $hours_until_pickup) {
    $api_key  = 'YOUR_OPENAI_API_KEY_HERE';
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $prompt = "A food listing says: \"$description\". "
            . "It expires in approximately " . round($hours_until_pickup, 1) . " hours. "
            . "Reply with exactly one word only: high, medium, or low.";

    return call_openai_api($api_key, $endpoint, $prompt, 5);
}


// ============================================================
// NATURAL LANGUAGE SEARCH
// Called when a user types a search sentence
// Returns an array of filters to apply to the database query
// ============================================================
function parse_natural_language_search($query) {
    $api_key  = 'YOUR_OPENAI_API_KEY_HERE';
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $prompt = "A user is searching for surplus food with this request: \"$query\"\n\n"
            . "Extract search filters from this and return ONLY a valid JSON object with these keys:\n"
            . "- category: one of [meals, bakery, produce, dairy, other] or null\n"
            . "- min_quantity: integer or null\n"
            . "- urgency: one of [high, medium, low] or null\n"
            . "- keyword: a short food keyword to search in title/description, or null\n\n"
            . "Return ONLY the JSON object. No explanation. No markdown. No extra text.";

    $response_text = call_openai_api($api_key, $endpoint, $prompt, 100);

    if ($response_text === null) return get_fallback_filters($query);

    $filters = json_decode($response_text, true);
    if (!is_array($filters)) return get_fallback_filters($query);

    return $filters;
}

// Simple keyword fallback if AI fails during demo
function get_fallback_filters($query) {
    $query_lower = strtolower($query);
    $categories = ['meals', 'bakery', 'produce', 'dairy', 'other'];
    $found_category = null;
    foreach ($categories as $cat) {
        if (strpos($query_lower, $cat) !== false) {
            $found_category = $cat;
            break;
        }
    }
    return [
        'category'     => $found_category,
        'min_quantity' => null,
        'urgency'      => null,
        'keyword'      => $query // use the whole query as keyword search
    ];
}


// ============================================================
// SHARED HELPER — makes the actual HTTP call to OpenAI
// ============================================================
function call_openai_api($api_key, $endpoint, $prompt, $max_tokens) {
    $payload = json_encode([
        "model"      => "gpt-4o-mini",
        "messages"   => [["role" => "user", "content" => $prompt]],
        "max_tokens" => $max_tokens
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8); // stop waiting after 8 seconds

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) return null;

    $data = json_decode($response, true);
    $text = trim($data['choices'][0]['message']['content'] ?? '');
    return $text !== '' ? $text : null;
}
?>
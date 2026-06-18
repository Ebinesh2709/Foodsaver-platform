<?php
// Sends listing info to an AI API and returns 'high', 'medium', or 'low'.
// Falls back to a simple rule if the API call fails, so the demo never breaks.

function get_urgency_score($description, $expiry_datetime) {
    $hours_until_expiry = (strtotime($expiry_datetime) - time()) / 3600;

    $ai_result = call_ai_api($description, $hours_until_expiry);
    if ($ai_result !== null) return $ai_result;

    if ($hours_until_expiry <= 12) return 'high';
    if ($hours_until_expiry <= 48) return 'medium';
    return 'low';
}

function call_ai_api($description, $hours_until_expiry) {
    $api_key = 'YOUR_API_KEY_HERE'; // never commit this to GitHub - keep it out of version control
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $prompt = "A food listing says: \"$description\". It expires in about "
            . round($hours_until_expiry, 1) . " hours. Reply with exactly one word: high, medium, or low.";

    $payload = json_encode([
        "model" => "gpt-4o-mini",
        "messages" => [["role" => "user", "content" => $prompt]],
        "max_tokens" => 5
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $api_key"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || !$response) return null;

    $data = json_decode($response, true);
    $text = strtolower(trim($data['choices'][0]['message']['content'] ?? ''));
    return in_array($text, ['high', 'medium', 'low']) ? $text : null;
}
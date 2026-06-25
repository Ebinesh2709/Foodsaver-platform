<?php
/**
 * AI Helper — only file that makes external HTTP requests to Gemini API.
 *
 * IMPORTANT: config/db.php must already be included by the caller so that
 * the GEMINI_API_KEY constant is available. Do NOT include db.php here.
 *
 * Depends on: includes/urgency_fallback.php (must be included by caller)
 */

function gemini_request(string $prompt, int $max_tokens): string {
    $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    $data = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0, 'maxOutputTokens' => $max_tokens]
    ]);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GEMINI_API_KEY,
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return ''; }
    curl_close($ch);
    $body = json_decode($response, true);
    return trim($body['candidates'][0]['content']['parts'][0]['text'] ?? '');
}

function get_urgency_score(string $description, string $pickup_end): string {
    $now = new DateTime();
    $end = new DateTime($pickup_end);
    $diff = $now->diff($end);
    $hours = ($diff->days * 24) + $diff->h + ($diff->i / 60);

    if ($hours <= 0) {
        return 'high';
    }

    $prompt = "You are a food waste urgency classifier for a food redistribution platform.
A food business has listed the following surplus food item:
Description: {$description}
Hours remaining until pickup deadline: {$hours}

Classify the urgency of collecting this food item.
Respond with exactly one word — either: high, medium, or low
- high: must be collected very soon (under 12 hours) or highly perishable
- medium: moderate urgency (12-48 hours)
- low: plenty of time (over 48 hours)
Only output the single word. No punctuation, no explanation.";

    $result = gemini_request($prompt, 5);
    $word = strtolower($result);

    if (in_array($word, ['high', 'medium', 'low'])) {
        return $word;
    }

    return calculate_urgency_fallback($hours);
}

function parse_natural_language_search(string $query): array {
    $fallback = [
        'category' => null,
        'min_quantity' => null,
        'urgency' => null,
        'keyword' => $query,
        'synonyms' => [],
        'intent_summary' => ''
    ];

    $prompt = "You are a smart food search assistant for a Sri Lankan food redistribution platform.
A user has typed this request: \"{$query}\"

Extract the following and return ONLY a valid JSON object:
{
  \"category\": one of meals/bakery/produce/dairy/other or null,
  \"min_quantity\": integer minimum quantity needed or null,
  \"urgency\": one of high/medium/low or null,
  \"keyword\": the primary food item name simplified (e.g. \"fried rice\" becomes \"rice\") or null,
  \"synonyms\": array of 2-3 alternative search terms for the same food or empty array,
  \"intent_summary\": one short sentence describing what the user needs (max 12 words)
}

Rules:
- For quantity: \"for 15 people\", \"15 portions\", \"need 15\" all mean min_quantity = 15
- For keyword: simplify to the core food type
- For synonyms: think of how a Sri Lankan food business might label this item
- Return only the JSON. No markdown, no backticks, no explanation.";

    $result = gemini_request($prompt, 200);
    
    // Clean up potential markdown formatting
    $result = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $result);
    $result = trim($result, "` \t\n\r\0\x0B");

    $decoded = json_decode($result, true);

    if (is_array($decoded)) {
        return [
            'category' => isset($decoded['category']) && in_array($decoded['category'], ['meals', 'bakery', 'produce', 'dairy', 'other']) ? $decoded['category'] : null,
            'min_quantity' => isset($decoded['min_quantity']) && is_numeric($decoded['min_quantity']) ? (int)$decoded['min_quantity'] : null,
            'urgency' => isset($decoded['urgency']) && in_array($decoded['urgency'], ['high', 'medium', 'low']) ? $decoded['urgency'] : null,
            'keyword' => isset($decoded['keyword']) ? (string)$decoded['keyword'] : null,
            'synonyms' => isset($decoded['synonyms']) && is_array($decoded['synonyms']) ? $decoded['synonyms'] : [],
            'intent_summary' => isset($decoded['intent_summary']) ? (string)$decoded['intent_summary'] : ''
        ];
    }

    return $fallback;
}

<?php
/**
 * AI Helper — only file that makes external HTTP requests to Gemini API.
 *
 * IMPORTANT: config/db.php must already be included by the caller so that
 * the GEMINI_API_KEY constant is available. Do NOT include db.php here.
 *
 * Depends on: includes/urgency_fallback.php (must be included by caller)
 */

function groq_request(string $prompt, int $max_tokens): string {
    $url  = 'https://api.groq.com/openai/v1/chat/completions';
    $data = json_encode([
        'model'       => 'llama-3.1-8b-instant',
        'messages'    => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0,
        'max_tokens'  => $max_tokens
    ]);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return ''; }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) { return ''; }
    $body = json_decode($response, true);
    return trim($body['choices'][0]['message']['content'] ?? '');
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

    $result = groq_request($prompt, 5);
    $word = strtolower($result);

    if (in_array($word, ['high', 'medium', 'low'])) {
        return $word;
    }

    return calculate_urgency_fallback($hours);
}

function parse_natural_language_search(string $query): array {
    // Improved Fallback Logic (Regex & Stop words)
    $min_qty = null;
    if (preg_match('/(\d+)/', $query, $matches)) {
        $min_qty = (int)$matches[1];
    }
    
    $stop_words = ['i', 'need', 'want', 'looking', 'for', 'some', 'any', 'a', 'an', 'the', 'get', 'buy', 'have'];
    $words = explode(' ', strtolower($query));
    $keywords = [];
    foreach ($words as $word) {
        $word = preg_replace('/[^a-z]/', '', $word);
        if (!empty($word) && !in_array($word, $stop_words)) {
            $keywords[] = $word;
        }
    }
    
    $fallback_keyword = implode(' ', $keywords);
    if (empty($fallback_keyword)) {
        $fallback_keyword = $query;
    }

    $fallback = [
        'category' => null,
        'min_quantity' => $min_qty,
        'urgency' => null,
        'keyword' => $fallback_keyword,
        'synonyms' => count($keywords) > 1 ? $keywords : [],
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

    $result = groq_request($prompt, 100);
    
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

function generate_listing_summary(string $title, string $description, string $category, float $discounted_price, int $quantity, string $pickup_end): string {
    $fallback = "Fresh {$category} available at a great price — grab it before it's gone!";

    $prompt = "You are writing friendly, appetising copy for a food waste reduction app in Sri Lanka.
A business has listed this surplus food:
Title: {$title}
Description: {$description}
Category: {$category}
Price: LKR {$discounted_price}
Quantity: {$quantity}
Pickup deadline: {$pickup_end}

Write one short friendly sentence (max 25 words) that makes a customer want to pick this up.
Focus on value: fresh food, good price, helping reduce waste.
No emojis. Plain text only. Do not start with \"I\" or \"This is\".";

    $result = groq_request($prompt, 60);
    return $result ?: $fallback;
}

function generate_search_response(string $intent_summary, int $result_count, int $min_quantity): string {
    if ($result_count > 0) {
        $fallback = "Found {$result_count} listing(s) matching your request.";
        $prompt = "A user searched for food on a food redistribution app.
Their need: {$intent_summary}
Matching listings found: {$result_count}
Minimum quantity they need: {$min_quantity}

Write a friendly 1-sentence response (max 15 words) confirming what was found.
Example: \"Great news — 3 listings can provide 15 or more rice portions right now.\"
Plain text only. No emojis.";
        $result = groq_request($prompt, 40);
        return $result ?: $fallback;
    } else {
        $fallback = "No exact matches found — try browsing all available listings below.";
        $prompt = "A user searched for food on a food redistribution app but nothing was found.
Their need: {$intent_summary}

Write a friendly 1-sentence suggestion (max 20 words) to try browsing all listings or adjusting search.
Plain text only. No emojis.";
        $result = groq_request($prompt, 50);
        return $result ?: $fallback;
    }
}

function get_expiry_alert(?string $pickup_end, ?string $urgency_score): string {
    if ($urgency_score !== 'high' || empty($pickup_end)) {
        return '';
    }

    $now = new DateTime();
    $end = new DateTime($pickup_end);
    $diff = $now->diff($end);
    $hours = round(($diff->days * 24) + $diff->h + ($diff->i / 60), 1);

    $fallback = "Pick up soon — only {$hours} hours left!";

    $prompt = "Write a short urgent but friendly alert (max 12 words) for a food pickup app.
The food must be collected within {$hours} hours or it will be wasted.
Make it feel urgent without being alarming. Plain text only. No emojis.";

    $result = groq_request($prompt, 30);
    return $result ?: $fallback;
}



<?php

/**
 * AI Helper — only file that makes external HTTP requests to Gemini API.
 *
 * IMPORTANT: config/db.php must already be included by the caller so that
 * the GEMINI_API_KEY constant is available. Do NOT include db.php here.
 *
 * Depends on: includes/urgency_fallback.php (must be included by caller)
 */

/**
 * Get an AI-scored urgency level for a food listing.
 *
 * @param  string $description  Food item description
 * @param  string $pickup_end   Datetime string (MySQL format or ISO)
 * @return string               'high', 'medium', or 'low'
 */
function get_urgency_score(string $description, string $pickup_end): string {
    $now              = new DateTime();
    $end              = new DateTime($pickup_end);
    $diff             = $now->diff($end);
    $hours_until_end  = ($diff->days * 24) + $diff->h + ($diff->i / 60);

    // If pickup has already passed, treat as high urgency immediately
    if ($end <= $now) {
        return 'high';
    }

    $payload = [
        'systemInstruction' => [
            'parts' => [
                ['text' => 'You are a food urgency classifier. Respond with exactly one word: high, medium, or low. Nothing else.']
            ]
        ],
        'contents' => [
            [
                'parts' => [
                    ['text' => "Food item: {$description}. Hours until pickup deadline: {$hours_until_end}. Classify urgency."]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 5,
            'temperature'     => 0,
        ]
    ];

    $result = _gemini_request($payload);

    if ($result !== null) {
        $word = strtolower(trim($result));
        if (in_array($word, ['high', 'medium', 'low'], true)) {
            return $word;
        }
    }

    // Fallback: rule-based
    return calculate_urgency_fallback($hours_until_end);
}

/**
 * Parse a natural-language search query into structured filters.
 *
 * @param  string $query  User's free-text search input
 * @return array{category: string|null, min_quantity: int|null, urgency: string|null, keyword: string|null}
 */
function parse_natural_language_search(string $query): array {
    $fallback = [
        'category'     => null,
        'min_quantity' => null,
        'urgency'      => null,
        'keyword'      => $query,
    ];

    $payload = [
        'systemInstruction' => [
            'parts' => [
                ['text' => 'You are a food search filter extractor. Extract search filters from the user query and return ONLY a JSON object with these keys: category (one of: meals, bakery, produce, dairy, other, or null), min_quantity (integer or null), urgency (one of: high, medium, low, or null), keyword (string or null). Return only the JSON object, no other text. Do not wrap in markdown tags like ```json.']
            ]
        ],
        'contents' => [
            [
                'parts' => [
                    ['text' => $query]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 100,
            'temperature'     => 0,
        ]
    ];

    $result = _gemini_request($payload);

    if ($result !== null) {
        // Sometimes Gemini returns the JSON wrapped in markdown code blocks. Clean it up.
        $result = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $result);
        $result = trim($result, "` \t\n\r\0\x0B");

        $decoded = json_decode($result, true);
        if (is_array($decoded) && array_key_exists('keyword', $decoded)) {
            return [
                'category'     => isset($decoded['category'])     && in_array($decoded['category'], ['meals','bakery','produce','dairy','other'], true) ? $decoded['category'] : null,
                'min_quantity' => isset($decoded['min_quantity']) && is_numeric($decoded['min_quantity']) ? (int)$decoded['min_quantity'] : null,
                'urgency'      => isset($decoded['urgency'])      && in_array($decoded['urgency'], ['high','medium','low'], true) ? $decoded['urgency'] : null,
                'keyword'      => isset($decoded['keyword'])      ? (string)$decoded['keyword'] : null,
            ];
        }
    }

    // Fallback: treat entire query as a keyword search
    return $fallback;
}

/**
 * Internal helper: send a request to the Gemini generateContent endpoint.
 * Returns the content string from candidates[0].content.parts[0].text, or null on failure.
 */
function _gemini_request(array $payload): ?string {
    if (!defined('GEMINI_API_KEY')) {
        return null;
    }

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;
    
    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error !== '' || $response === false) {
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return null;
    }

    return (string)$data['candidates'][0]['content']['parts'][0]['text'];
}


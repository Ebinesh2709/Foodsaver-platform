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

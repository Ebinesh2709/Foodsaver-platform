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

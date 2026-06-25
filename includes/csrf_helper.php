<?php

/**
 * Generate a CSRF token and store it in the session.
 * Returns the token string.
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the submitted CSRF token against the one stored in session.
 * Sends 403 and exits on mismatch.
 */
function verify_csrf_token(string $token): void {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo 'CSRF verification failed';
        exit;
    }
}

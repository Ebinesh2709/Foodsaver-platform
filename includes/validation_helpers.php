<?php

/**
 * Pure validation helper functions — no DB connection, no session.
 * Tested directly by ValidationTest.php.
 */

/**
 * Validate a food listing title.
 * Must be non-empty and at most 200 characters.
 */
function validate_title(string $title): bool {
    $title = trim($title);
    return $title !== '' && mb_strlen($title) <= 200;
}

/**
 * Validate an email address using PHP's built-in filter.
 */
function validate_email(string $email): bool {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate a password: minimum 8 characters.
 */
function validate_password(string $password): bool {
    return mb_strlen($password) >= 8;
}

/**
 * Validate a quantity: must be a positive integer.
 */
function validate_quantity(mixed $quantity): bool {
    return is_numeric($quantity) && (int)$quantity > 0;
}

/**
 * Validate a pickup time window.
 * pickup_end must be after pickup_start.
 * Both must be valid datetime strings.
 *
 * @param  string $pickup_start  Datetime string
 * @param  string $pickup_end    Datetime string
 * @return bool
 */
function validate_pickup_window(string $pickup_start, string $pickup_end): bool {
    $start = strtotime($pickup_start);
    $end   = strtotime($pickup_end);
    if ($start === false || $end === false) {
        return false;
    }
    return $end > $start;
}

/**
 * Escape a value for safe HTML output.
 * Wraps htmlspecialchars with ENT_QUOTES and UTF-8.
 */
function sanitize_output(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

<?php
function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
function validate_password(string $password): bool {
    return strlen($password) >= 8;
}
function validate_quantity(int $quantity): bool {
    return $quantity > 0;
}
function validate_pickup_window(string $start, string $end): bool {
    return strtotime($end) > strtotime($start);
}
function sanitize_output(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function validate_title(string $title): bool {
    return trim($title) !== '' && mb_strlen($title) <= 200;
}

<?php

/**
 * Pure urgency fallback function — no API call, no DB.
 * Used by ai_helper.php and tested directly by UrgencyTest.php.
 *
 * @param  float  $hours  Hours until pickup deadline (0 or negative = already expired)
 * @return string         'high', 'medium', or 'low'
 */
function calculate_urgency_fallback(float $hours): string {
    if ($hours <= 12) {
        return 'high';
    }
    if ($hours <= 48) {
        return 'medium';
    }
    return 'low';
}

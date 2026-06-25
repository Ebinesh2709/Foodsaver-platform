<?php
function calculate_urgency_fallback(float $hours): string {
    if ($hours <= 12) return 'high';
    if ($hours <= 48) return 'medium';
    return 'low';
}

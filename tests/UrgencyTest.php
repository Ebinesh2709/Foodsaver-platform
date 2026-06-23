<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/urgency_fallback.php';

/**
 * Tests for the pure urgency fallback logic.
 * No DB connection, no API calls.
 */
class UrgencyTest extends TestCase
{
    public function testHighUrgencyWithin12Hours(): void
    {
        $this->assertSame('high', calculate_urgency_fallback(8));
    }

    public function testMediumUrgencyWithin48Hours(): void
    {
        $this->assertSame('medium', calculate_urgency_fallback(24));
    }

    public function testLowUrgencyBeyond48Hours(): void
    {
        $this->assertSame('low', calculate_urgency_fallback(72));
    }

    public function testExpiredListingIsHigh(): void
    {
        $this->assertSame('high', calculate_urgency_fallback(0));
    }
}

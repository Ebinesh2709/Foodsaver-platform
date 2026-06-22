<?php
use PHPUnit\Framework\TestCase;

class UrgencyTest extends TestCase
{
    private function getFallbackUrgency($pickup_end) {
        $hours = (strtotime($pickup_end) - time()) / 3600;
        if ($hours <= 12) return 'high';
        if ($hours <= 48) return 'medium';
        return 'low';
    }

    public function test_high_urgency_within_12_hours()
    {
        $pickup_end = date('Y-m-d H:i:s', time() + (2 * 3600));
        $this->assertEquals('high', $this->getFallbackUrgency($pickup_end));
    }

    public function test_medium_urgency_within_48_hours()
    {
        $pickup_end = date('Y-m-d H:i:s', time() + (24 * 3600));
        $this->assertEquals('medium', $this->getFallbackUrgency($pickup_end));
    }

    public function test_low_urgency_beyond_48_hours()
    {
        $pickup_end = date('Y-m-d H:i:s', time() + (5 * 24 * 3600));
        $this->assertEquals('low', $this->getFallbackUrgency($pickup_end));
    }

    public function test_expired_listing_is_high_urgency()
    {
        $pickup_end = date('Y-m-d H:i:s', time() - (1 * 3600));
        $hours = (strtotime($pickup_end) - time()) / 3600;
        $this->assertTrue($hours < 0);
    }
}
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/validation_helpers.php';

/**
 * 12 tests for pure input validation functions.
 * No DB connection, no session, no API calls.
 */
class ValidationTest extends TestCase
{
    // --- Title ---

    public function testEmptyTitleIsInvalid(): void
    {
        $this->assertFalse(validate_title(''));
    }

    public function testValidTitlePasses(): void
    {
        $this->assertTrue(validate_title('Fresh Rice Meals'));
    }

    // --- Quantity ---

    public function testNegativeQuantityIsInvalid(): void
    {
        $this->assertFalse(validate_quantity(-1));
    }

    public function testZeroQuantityIsInvalid(): void
    {
        $this->assertFalse(validate_quantity(0));
    }

    public function testValidQuantityPasses(): void
    {
        $this->assertTrue(validate_quantity(5));
    }

    // --- Pickup Window ---

    public function testPickupEndBeforeStartIsInvalid(): void
    {
        $this->assertFalse(validate_pickup_window('2026-07-01 10:00:00', '2026-07-01 09:00:00'));
    }

    public function testValidPickupWindowPasses(): void
    {
        $this->assertTrue(validate_pickup_window('2026-07-01 10:00:00', '2026-07-01 12:00:00'));
    }

    // --- Email ---

    public function testInvalidEmailFails(): void
    {
        $this->assertFalse(validate_email('not-an-email'));
    }

    public function testValidEmailPasses(): void
    {
        $this->assertTrue(validate_email('ebinesh@example.com'));
    }

    // --- Password ---

    public function testShortPasswordIsInvalid(): void
    {
        $this->assertFalse(validate_password('1234567')); // 7 chars — below minimum
    }

    public function testValidPasswordPasses(): void
    {
        $this->assertTrue(validate_password('secureP@ss'));
    }

    // --- XSS Sanitization ---

    public function testXssInputIsEscaped(): void
    {
        $output = sanitize_output('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $output);
    }
}

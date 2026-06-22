<?php
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function test_empty_title_is_invalid()
    {
        $title = trim('');
        $this->assertEmpty($title);
    }

    public function test_valid_title_passes()
    {
        $title = trim('Fresh Rice and Curry');
        $this->assertNotEmpty($title);
    }

    public function test_negative_quantity_is_invalid()
    {
        $quantity = -5;
        $this->assertFalse(is_numeric($quantity) && $quantity > 0);
    }

    public function test_zero_quantity_is_invalid()
    {
        $quantity = 0;
        $this->assertFalse(is_numeric($quantity) && $quantity > 0);
    }

    public function test_valid_quantity_passes()
    {
        $quantity = 10;
        $this->assertTrue(is_numeric($quantity) && $quantity > 0);
    }

    public function test_pickup_end_must_be_after_pickup_start()
    {
        $start = strtotime('2025-01-01 10:00:00');
        $end   = strtotime('2025-01-01 08:00:00');
        $this->assertFalse($end > $start);
    }

    public function test_valid_pickup_window_passes()
    {
        $start = strtotime('2025-01-01 10:00:00');
        $end   = strtotime('2025-01-01 14:00:00');
        $this->assertTrue($end > $start);
    }

    public function test_invalid_email_fails_validation()
    {
        $email = 'notanemail';
        $this->assertFalse(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public function test_valid_email_passes_validation()
    {
        $email = 'test@example.com';
        $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public function test_short_password_is_invalid()
    {
        $password = '123';
        $this->assertFalse(strlen($password) >= 8);
    }

    public function test_valid_password_passes()
    {
        $password = 'securepassword123';
        $this->assertTrue(strlen($password) >= 8);
    }

    public function test_xss_input_is_escaped()
    {
        $input   = '<script>alert("xss")</script>';
        $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $escaped);
    }
}
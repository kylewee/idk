<?php
/**
 * Phone number utilities
 * Shared functions for phone number handling and normalization
 */

/**
 * Normalize a phone number into E.164 format for Twilio.
 *
 * @param mixed $value Raw phone number input
 * @return string|null Normalized E.164 phone number or null if invalid
 */
function normalize_phone($value): ?string
{
    if (!is_scalar($value)) {
        return null;
    }
    $raw = trim((string)$value);
    if ($raw === '') {
        return null;
    }

    // Already in E.164 format
    if ($raw[0] === '+') {
        $digits = '+' . preg_replace('/[^0-9]/', '', substr($raw, 1));
        return strlen($digits) >= 10 ? $digits : null;
    }

    // Extract only digits
    $digits = preg_replace('/\D+/', '', $raw);
    $len = strlen($digits);

    // 10-digit US number
    if ($len === 10) {
        return '+1' . $digits;
    }

    // 11-digit number starting with 1 (US)
    if ($len === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }

    // International number (10-15 digits)
    if ($len >= 10 && $len <= 15) {
        return '+' . $digits;
    }

    return null;
}

/**
 * Format phone number for display (US format)
 *
 * @param string $e164 E.164 formatted phone number
 * @return string Formatted phone number
 */
function format_phone_display(string $e164): string
{
    $digits = preg_replace('/\D+/', '', $e164);

    if (strlen($digits) === 11 && $digits[0] === '1') {
        $digits = substr($digits, 1);
    }

    if (strlen($digits) === 10) {
        return sprintf('(%s) %s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6)
        );
    }

    return $e164;
}

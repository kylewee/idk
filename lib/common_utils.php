<?php
/**
 * Common utilities
 * General-purpose helper functions
 */

/**
 * Convert various opt-in values ("true", 1, "yes") into a boolean flag
 *
 * @param mixed $value Value to convert
 * @return bool Boolean representation
 */
function to_bool($value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return (int)$value !== 0;
    }

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    return false;
}

/**
 * Sanitize string input
 *
 * @param mixed $value Value to sanitize
 * @param string $default Default value if empty
 * @return string Sanitized string
 */
function sanitize_string($value, string $default = ''): string
{
    if (!is_scalar($value)) {
        return $default;
    }

    $str = trim((string)$value);
    return $str !== '' ? $str : $default;
}

/**
 * Sanitize integer input
 *
 * @param mixed $value Value to sanitize
 * @param int $default Default value if invalid
 * @return int Sanitized integer
 */
function sanitize_int($value, int $default = 0): int
{
    if (is_numeric($value)) {
        return (int)$value;
    }
    return $default;
}

/**
 * Split full name into first and last name
 *
 * @param string $fullName Full name string
 * @return array Array with 'first' and 'last' keys
 */
function split_name(string $fullName): array
{
    $name = trim($fullName);
    $first = $name;
    $last = '';

    if (strpos($name, ' ') !== false) {
        $parts = preg_split('/\s+/', $name);
        $last = array_pop($parts);
        $first = trim(implode(' ', $parts)) ?: $name;
    }

    return [
        'first' => $first,
        'last' => $last ?: $first,
        'full' => $name,
    ];
}

/**
 * Log structured data to file
 *
 * @param string $logFile Log file path
 * @param string $level Log level (info, warning, error)
 * @param string $type Log type/category
 * @param array $data Log data
 * @return bool Success status
 */
function log_structured(string $logFile, string $level, string $type, array $data): bool
{
    $entry = [
        'timestamp' => date('c'),
        'level' => $level,
        'type' => $type,
        'data' => $data,
    ];

    $line = json_encode($entry) . PHP_EOL;

    return file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Make HTTP request with curl
 *
 * @param string $url Request URL
 * @param array $options Curl options
 * @return array Response with 'body', 'http_code', 'error' keys
 */
function http_request(string $url, array $options = []): array
{
    if (!function_exists('curl_init')) {
        return [
            'body' => null,
            'http_code' => 0,
            'error' => 'curl extension not available',
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($options as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $body = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'body' => $body,
        'http_code' => $httpCode,
        'error' => $error ?: null,
    ];
}

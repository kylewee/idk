<?php
/**
 * Twilio utilities
 * Functions for SMS and voice integrations
 */

require_once __DIR__ . '/phone_utils.php';

/**
 * Send SMS message via Twilio
 *
 * @param string $to Destination phone number
 * @param string $body Message body
 * @param array $options Additional options (from, messagingServiceSid)
 * @return array Result with 'status', 'http_code', 'response', 'error' keys
 */
function send_twilio_sms(string $to, string $body, array $options = []): array
{
    if (!function_exists('curl_init')) {
        return ['status' => 'error', 'reason' => 'curl_missing'];
    }

    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN')) {
        return ['status' => 'error', 'reason' => 'twilio_config_missing'];
    }

    // Normalize destination number
    $normalizedTo = normalize_phone($to);
    if ($normalizedTo === null) {
        return ['status' => 'error', 'reason' => 'invalid_destination', 'detail' => $to];
    }

    // Determine sender (from number or messaging service)
    $messagingServiceSid = $options['messagingServiceSid'] ?? null;
    $from = $options['from'] ?? null;

    if (!$messagingServiceSid && !$from) {
        // Try to get from config
        $fromConfig = defined('TWILIO_SMS_FROM') && TWILIO_SMS_FROM
            ? (string)TWILIO_SMS_FROM
            : (defined('TWILIO_CALLER_ID') && TWILIO_CALLER_ID ? (string)TWILIO_CALLER_ID : null);

        if ($fromConfig) {
            $from = normalize_phone($fromConfig);
        }
    }

    if (!$messagingServiceSid && !$from) {
        return ['status' => 'error', 'reason' => 'twilio_from_missing'];
    }

    // Build payload
    $payloadFields = [
        'To' => $normalizedTo,
        'Body' => $body,
    ];

    if ($messagingServiceSid) {
        $payloadFields['MessagingServiceSid'] = $messagingServiceSid;
    } else {
        $payloadFields['From'] = $from;
    }

    $payload = http_build_query($payloadFields);

    // Send request
    $url = 'https://api.twilio.com/2010-04-01/Accounts/'
        . rawurlencode((string)TWILIO_ACCOUNT_SID)
        . '/Messages.json';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => (string)TWILIO_ACCOUNT_SID . ':' . (string)TWILIO_AUTH_TOKEN,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = [
        'status' => $httpCode >= 200 && $httpCode < 300 ? 'success' : 'error',
        'http_code' => $httpCode,
        'response' => $response,
    ];

    if ($curlError) {
        $result['error'] = $curlError;
    }

    return $result;
}

/**
 * Send quote SMS to customer
 *
 * @param array $lead Lead data (name, phone, repair, vehicle info)
 * @param array $estimateSummary Estimate summary from build_estimate_summary()
 * @return array Result from send_twilio_sms()
 */
function send_quote_sms(array $lead, array $estimateSummary): array
{
    // Extract customer info
    $name = isset($lead['name']) && trim((string)$lead['name']) !== ''
        ? trim((string)$lead['name'])
        : 'there';

    $repair = isset($lead['repair']) && trim((string)$lead['repair']) !== ''
        ? trim((string)$lead['repair'])
        : (isset($lead['service']) ? trim((string)$lead['service']) : 'your vehicle');

    // Build vehicle description
    $vehicleParts = [];
    foreach (['year', 'make', 'model'] as $key) {
        if (!empty($lead[$key])) {
            $vehicleParts[] = trim((string)$lead[$key]);
        }
    }
    $vehicle = implode(' ', $vehicleParts) ?: 'your vehicle';

    // Extract estimate amount
    $amount = isset($estimateSummary['amount']) && is_numeric($estimateSummary['amount'])
        ? (float)$estimateSummary['amount']
        : null;

    // Build message body
    if ($amount !== null && $amount > 0) {
        $priceText = '$' . number_format($amount, 0);
        $body = "Hi {$name}, thanks for contacting Mechanics Saint Augustine. "
            . "Estimated price for {$repair} on {$vehicle} is {$priceText}.";
    } else {
        $body = "Hi {$name}, thanks for contacting Mechanics Saint Augustine. "
            . "We received your request for {$repair} on {$vehicle}.";
    }

    $body .= ' Reply STOP to opt out.';

    // Send SMS
    return send_twilio_sms($lead['phone'], $body);
}

/**
 * Make Twilio API request
 *
 * @param string $endpoint API endpoint path (e.g., 'Calls.json')
 * @param array $data Request data
 * @param string $method HTTP method (GET or POST)
 * @return array Response with 'status', 'http_code', 'body', 'error' keys
 */
function twilio_api_request(string $endpoint, array $data = [], string $method = 'POST'): array
{
    if (!function_exists('curl_init')) {
        return ['status' => 'error', 'reason' => 'curl_missing'];
    }

    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN')) {
        return ['status' => 'error', 'reason' => 'twilio_config_missing'];
    }

    $url = 'https://api.twilio.com/2010-04-01/Accounts/'
        . rawurlencode((string)TWILIO_ACCOUNT_SID)
        . '/' . ltrim($endpoint, '/');

    $ch = curl_init();

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    } else {
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => (string)TWILIO_ACCOUNT_SID . ':' . (string)TWILIO_AUTH_TOKEN,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $httpCode >= 200 && $httpCode < 300 ? 'success' : 'error',
        'http_code' => $httpCode,
        'body' => $response,
        'error' => $curlError ?: null,
    ];
}

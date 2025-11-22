<?php
// Quote intake endpoint: validates input, calls estimator, and (new) creates a CRM lead
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/PricingService.php';

/**
 * Convert the various opt-in values ("true", 1, "yes") into a boolean flag.
 */
function qi_to_bool($value): bool
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
 * Normalize a phone number into E.164 for Twilio.
 */
function qi_normalize_phone($value): ?string
{
    if (!is_scalar($value)) {
        return null;
    }
    $raw = trim((string)$value);
    if ($raw === '') {
        return null;
    }
    if ($raw[0] === '+') {
        $digits = '+' . preg_replace('/[^0-9]/', '', substr($raw, 1));
        return strlen($digits) >= 10 ? $digits : null;
    }
    $digits = preg_replace('/\D+/', '', $raw);
    $len = strlen($digits);
    if ($len === 10) {
        return '+1' . $digits;
    }
    if ($len === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }
    if ($len >= 10 && $len <= 15) {
        return '+' . $digits;
    }
    return null;
}

/**
 * Calculate pricing estimate using centralized PricingService.
 * Replaces the old hardcoded pricing matrix.
 */
function qi_local_estimate(array $lead): ?array
{
    $repair = '';
    if (!empty($lead['repair'])) {
        $repair = trim((string)$lead['repair']);
    } elseif (!empty($lead['service'])) {
        $repair = trim((string)$lead['service']);
    }

    if ($repair === '') {
        return null;
    }

    try {
        $pricing = PricingService::getInstance();
        $estimate = $pricing->calculateEstimateFromVehicle($repair, [
            'year' => $lead['year'] ?? null,
            'engine_size' => $lead['engine'] ?? null,
        ]);

        if (!$estimate) {
            return null;
        }

        return [
            'amount' => (float)$estimate['final_price'],
            'source' => 'pricing_service',
            'base_price' => $estimate['base_price'],
            'multiplier' => $estimate['price_multiplier'],
            'repair_key' => $estimate['repair'],
        ];
    } catch (Exception $e) {
        error_log('Pricing calculation error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Extract a numeric candidate from the internal estimator payload.
 */
function qi_remote_estimate_candidate($estimate)
{
    if (!is_array($estimate)) {
        return null;
    }
    $candidates = [];
    $low = isset($estimate['total_low']) && is_numeric($estimate['total_low']) ? (float)$estimate['total_low'] : null;
    $high = isset($estimate['total_high']) && is_numeric($estimate['total_high']) ? (float)$estimate['total_high'] : null;
    if ($low !== null && $high !== null && $low > 0 && $high > 0) {
        $candidates[] = [
            'amount' => ($low + $high) / 2,
            'source' => 'remote_range_avg',
            'details' => ['low' => $low, 'high' => $high],
        ];
    }
    foreach (['total', 'estimate', 'price', 'amount', 'total_mid'] as $key) {
        if (isset($estimate[$key]) && is_numeric($estimate[$key])) {
            $candidates[] = [
                'amount' => (float)$estimate[$key],
                'source' => 'remote_' . $key,
            ];
        }
    }
    if (empty($candidates)) {
        foreach ($estimate as $key => $value) {
            if (is_numeric($value)) {
                $candidates[] = [
                    'amount' => (float)$value,
                    'source' => 'remote_' . $key,
                ];
                break;
            }
        }
    }
    if (empty($candidates)) {
        return null;
    }
    foreach ($candidates as $candidate) {
        if ($candidate['amount'] > 0) {
            $candidate['amount'] = round($candidate['amount']);
            return $candidate;
        }
    }
    $first = $candidates[0];
    $first['amount'] = round($first['amount']);
    return $first;
}

/**
 * Merge local and remote estimates for downstream logging + messaging.
 */
function qi_build_estimate_summary($estimate, array $lead): array
{
    $remote = qi_remote_estimate_candidate($estimate);
    $local = qi_local_estimate($lead);
    $summary = [
        'amount' => null,
        'source' => null,
        'candidates' => [],
    ];
    if ($remote) {
        $summary['candidates']['remote'] = $remote;
        if ($remote['amount'] > 0) {
            $summary['amount'] = $remote['amount'];
            $summary['source'] = $remote['source'];
        }
    }
    if ($local) {
        $summary['candidates']['local'] = $local;
        if ($summary['amount'] === null && $local['amount'] > 0) {
            $summary['amount'] = round((float)$local['amount']);
            $summary['source'] = $local['source'];
        }
    }
    if ($summary['amount'] !== null) {
        $summary['amount'] = (float)round($summary['amount']);
    }
    if (is_array($estimate)) {
        $summary['raw_remote'] = $estimate;
    }
    return $summary;
}

/**
 * Send a one-time SMS via Twilio when the lead opts in.
 */
function qi_send_sms_quote(array $lead, array $estimateSummary): array
{
    if (!function_exists('curl_init')) {
        return ['status' => 'error', 'reason' => 'curl_missing'];
    }
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN')) {
        return ['status' => 'error', 'reason' => 'twilio_config_missing'];
    }
    $messagingServiceSid = defined('TWILIO_MESSAGING_SERVICE_SID') && TWILIO_MESSAGING_SERVICE_SID
        ? trim((string)TWILIO_MESSAGING_SERVICE_SID)
        : '';

    $fromConfig = defined('TWILIO_SMS_FROM') && TWILIO_SMS_FROM ? (string)TWILIO_SMS_FROM : '';
    if ($fromConfig === '' && defined('TWILIO_CALLER_ID') && TWILIO_CALLER_ID) {
        $fromConfig = (string)TWILIO_CALLER_ID;
    }
    $from = $fromConfig !== '' ? qi_normalize_phone($fromConfig) : null;

    if ($messagingServiceSid === '' && $from === null) {
        return ['status' => 'error', 'reason' => 'twilio_from_missing'];
    }
    $toRaw = $lead['phone'] ?? '';
    $to = qi_normalize_phone($toRaw);
    if ($to === null) {
        return ['status' => 'error', 'reason' => 'invalid_destination', 'detail' => $toRaw];
    }
    $name = isset($lead['name']) && trim((string)$lead['name']) !== '' ? trim((string)$lead['name']) : 'there';
    $repair = isset($lead['repair']) && trim((string)$lead['repair']) !== '' ? trim((string)$lead['repair']) : (isset($lead['service']) ? trim((string)$lead['service']) : 'your vehicle');
    $vehicleParts = [];
    foreach (['year', 'make', 'model'] as $key) {
        if (!empty($lead[$key])) {
            $vehicleParts[] = trim((string)$lead[$key]);
        }
    }
    $vehicle = implode(' ', $vehicleParts);
    if ($vehicle === '') {
        $vehicle = 'your vehicle';
    }
    $slotInfo = null;
    if (isset($lead['_preferred_slot']) && is_array($lead['_preferred_slot']) && isset($lead['_preferred_slot']['display'])) {
        $slotInfo = $lead['_preferred_slot']['display'];
    } elseif (isset($lead['preferred_slot'])) {
        $slot = qi_normalize_slot($lead['preferred_slot']);
        if ($slot && isset($slot['display'])) {
            $slotInfo = $slot['display'];
        }
    }
    $amount = isset($estimateSummary['amount']) && is_numeric($estimateSummary['amount']) ? (float)$estimateSummary['amount'] : null;
    if ($amount !== null && $amount > 0) {
        $priceText = '$' . number_format($amount, 0);
        $body = "Hi {$name}, thanks for contacting Mechanics Saint Augustine. Estimated price for {$repair} on {$vehicle} is {$priceText}.";
    } else {
        $body = "Hi {$name}, thanks for contacting Mechanics Saint Augustine. We received your request for {$repair} on {$vehicle}.";
    }
    if ($slotInfo) {
        $body .= " Preferred time: {$slotInfo}.";
    }
    $body .= ' Reply STOP to opt out.';

    $payloadFields = [
        'To' => $to,
        'Body' => $body,
    ];
    if ($messagingServiceSid !== '') {
        $payloadFields['MessagingServiceSid'] = $messagingServiceSid;
    } else {
        $payloadFields['From'] = $from;
    }

    $payload = http_build_query($payloadFields);

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode((string)TWILIO_ACCOUNT_SID) . '/Messages.json';
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
    $curlErrno = curl_errno($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [
            'status' => 'error',
            'reason' => 'twilio_request_failed',
            'curl_errno' => $curlErrno,
            'curl_error' => $curlError,
        ];
    }

    $decoded = json_decode((string)$response, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        $result = [
            'status' => 'sent',
            'sid' => isset($decoded['sid']) ? (string)$decoded['sid'] : null,
            'http' => $httpCode,
            'to' => $to,
            'body_preview' => substr($body, 0, 120),
        ];
        if ($messagingServiceSid !== '') {
            $result['messaging_service'] = $messagingServiceSid;
            if (isset($decoded['from'])) {
                $result['from'] = (string)$decoded['from'];
            }
        } else {
            $result['from'] = $from;
        }
        return $result;
    }

    return [
        'status' => 'error',
        'reason' => 'twilio_http_' . $httpCode,
        'http' => $httpCode,
        'twilio_response' => $decoded ?: (string)$response,
        'curl_errno' => $curlErrno,
        'curl_error' => $curlError,
    ];
}

/**
 * Normalize the preferred slot into ISO + localized display string.
 */
function qi_normalize_slot($value): ?array
{
    if (!is_string($value) || trim($value) === '') {
        return null;
    }
    $ts = strtotime($value);
    if ($ts === false || $ts <= time()) {
        return null;
    }
    try {
        $utc = (new DateTimeImmutable('@' . $ts))->setTimezone(new DateTimeZone('UTC'));
        $local = $utc->setTimezone(new DateTimeZone('America/New_York'));
        return [
            'iso' => $utc->format(DateTimeInterface::ATOM),
            'display' => $local->format('D, M j \a\t g:ia T'),
        ];
    } catch (Throwable $e) {
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$required = ['name', 'phone'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$textOptIn = false;
foreach (['text_opt_in', 'textQuote', 'sms_opt_in', 'notify_by_text'] as $optKey) {
    if (array_key_exists($optKey, $data)) {
        $textOptIn = qi_to_bool($data[$optKey]);
        break;
    }
}

$preferredSlot = isset($data['preferred_slot']) ? qi_normalize_slot($data['preferred_slot']) : null;

// Extract repair/service info for estimate
$estimate_payload = [
    'repair' => $data['repair'] ?? '',
    'service' => $data['service'] ?? '',
    'year' => (int)($data['year'] ?? 0),
    'make' => $data['make'] ?? '',
    'model' => $data['model'] ?? '',
    'engine' => $data['engine'] ?? '',
    'zip' => $data['zip'] ?? ''
];

// Call internal estimate API
$estimate_result = null;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8091/api/estimate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($estimate_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

$estimate_response = curl_exec($ch);
$estimate_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($estimate_response && $estimate_http_code === 200) {
    $estimate_result = json_decode($estimate_response, true);
}

$estimate_summary = qi_build_estimate_summary($estimate_result, $data);
$sms_lead_context = $data;
$sms_lead_context['_preferred_slot'] = $preferredSlot;
$sms_result = $textOptIn
    ? qi_send_sms_quote($sms_lead_context, $estimate_summary)
    : ['status' => 'skipped', 'reason' => 'not_opted_in'];

// Log the quote request (structured for easy parsing)
$log_entry = [
    'timestamp' => date('c'),
    'level' => 'info',
    'type' => 'quote_intake',
    'data' => [
        'name' => $data['name'],
        'phone' => $data['phone'],
        'email' => $data['email'] ?? '',
        'year' => $data['year'] ?? '',
        'make' => $data['make'] ?? '',
        'model' => $data['model'] ?? '',
        'engine' => $data['engine'] ?? '',
        'repair' => $data['repair'] ?? '',
        'zip' => $data['zip'] ?? '',
        'text_opt_in' => $textOptIn,
        'preferred_slot' => $preferredSlot,
        'estimate' => $estimate_summary,
        'estimate_raw' => $estimate_result,
        'sms' => $sms_result,
    ]
];

// Log to error_log (will go to PHP error log or syslog)
error_log('QUOTE_INTAKE: ' . json_encode($log_entry));

// Mirror log locally so we can tail it without server access hurdles
@file_put_contents(__DIR__ . '/../api/quote_intake.log', json_encode($log_entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);

// --- Create CRM Lead (if CRM config present) ---
$crm_result = null;
if (
    defined('CRM_API_URL') && CRM_API_URL &&
    defined('CRM_LEADS_ENTITY_ID') && (int)CRM_LEADS_ENTITY_ID > 0
) {
    if (!function_exists('curl_init')) {
        $crm_result = ['skipped' => true, 'reason' => 'curl extension not available'];
    } else {
        // Split name
        $name = trim((string)$data['name']);
        $first = $name; $last = '';
        if (strpos($name, ' ') !== false) {
            $bits = preg_split('/\s+/', $name);
            $last = array_pop($bits);
            $first = trim(implode(' ', $bits)) ?: $name;
        }

        // Build field values based on mapping
        $fieldValues = [];
        if (defined('CRM_FIELD_MAP') && is_array(CRM_FIELD_MAP)) {
            $mapf = CRM_FIELD_MAP;
            $assign = function(string $key, $val) use (&$fieldValues, $mapf) {
                if (isset($mapf[$key]) && (int)$mapf[$key] > 0 && $val !== '' && $val !== null) {
                    $fieldValues[(int)$mapf[$key]] = (string)$val;
                }
            };
            $assign('first_name', $first);
            $assign('last_name',  $last ?: $first);
            $assign('name',       $name);
            $assign('phone',      (string)($data['phone'] ?? ''));
            $assign('email',      (string)($data['email'] ?? ''));
            $assign('year',       !empty($data['year']) ? (string)$data['year'] : '');
            $assign('make',       (string)($data['make'] ?? ''));
            $assign('model',      (string)($data['model'] ?? ''));
            $assign('engine',     (string)($data['engine'] ?? ''));
            $assign('repair',     (string)($data['repair'] ?? ''));
            if ($preferredSlot && isset($preferredSlot['display'])) {
                $assign('preferred_slot', $preferredSlot['display']);
            }
            if (isset($estimate_summary['amount']) && $estimate_summary['amount'] !== null) {
                $assign('estimate', (string)round((float)$estimate_summary['amount']));
            } elseif (is_array($estimate_result)) {
                $assign('estimate', (string)($estimate_result['total_high'] ?? $estimate_result['total_low'] ?? ''));
            }
            // If a 'notes' field is mapped, include a compact summary
            if (isset($mapf['notes'])) {
                $estimateNote = '';
                if (isset($estimate_summary['amount']) && $estimate_summary['amount'] !== null) {
                    $estimateNote = '$' . number_format((float)$estimate_summary['amount'], 0) . ' (' . ($estimate_summary['source'] ?? 'estimate') . ')';
                } elseif (is_array($estimate_result)) {
                    $estimateNote = json_encode($estimate_result);
                }
                $notes = 'Quote via website' . "\n" .
                         'Phone: ' . ($data['phone'] ?? '') . "\n" .
                         'Email: ' . ($data['email'] ?? '') . "\n" .
                         'Vehicle: ' . ($data['year'] ?? '') . ' ' . ($data['make'] ?? '') . ' ' . ($data['model'] ?? '') . ' ' . ($data['engine'] ?? '') . "\n" .
                         'Repair: ' . ($data['repair'] ?? '') . "\n" .
                         'Preferred Slot: ' . ($preferredSlot['display'] ?? 'not selected') . "\n" .
                         'Estimate: ' . $estimateNote;
                $assign('notes', $notes);
            }
        }

        // Prepare POST
        $post = [
            'action'    => 'add_item',
            'entity_id' => (int)CRM_LEADS_ENTITY_ID,
        ];

        // Authenticate (token via username/password preferred, else API key if configured)
        if (defined('CRM_USERNAME') && CRM_USERNAME && defined('CRM_PASSWORD') && CRM_PASSWORD) {
            $loginCh = curl_init(CRM_API_URL);
            curl_setopt_array($loginCh, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    'action'   => 'login',
                    'username' => CRM_USERNAME,
                    'password' => CRM_PASSWORD,
                    // Some Rukovoditel setups require API key even for login
                    'key'      => (defined('CRM_API_KEY') ? CRM_API_KEY : ''),
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 15,
            ]);
            $loginResp = curl_exec($loginCh);
            $loginHttp = (int)curl_getinfo($loginCh, CURLINFO_HTTP_CODE);
            $loginErr = curl_error($loginCh);
            curl_close($loginCh);
            $loginJson = json_decode((string)$loginResp, true);
            if (is_array($loginJson) && !empty($loginJson['token'])) {
                $post['token'] = (string)$loginJson['token'];
            } elseif (defined('CRM_API_KEY') && CRM_API_KEY) {
                $post['key'] = CRM_API_KEY;
            } else {
                $crm_result = ['error' => 'CRM auth failed', 'login_http' => $loginHttp, 'login_body' => $loginResp, 'login_err' => $loginErr];
            }
        } elseif (defined('CRM_API_KEY') && CRM_API_KEY) {
            $post['key'] = CRM_API_KEY;
        }

        // Attach fields
        foreach ($fieldValues as $fid => $val) {
            $post['fields[field_' . (int)$fid . ']'] = $val;
        }

        if (!isset($crm_result['error'])) {
            $ch2 = curl_init(CRM_API_URL);
            curl_setopt_array($ch2, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 15,
            ]);
            $resp2 = curl_exec($ch2);
            $errno2 = curl_errno($ch2);
            $err2  = curl_error($ch2);
            $http2 = (int)curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);
            $crm_result = [ 'http' => $http2, 'curl_errno' => $errno2, 'curl_error' => $err2, 'body' => $resp2 ];
        }
    }
}

// Build response
$response = [
    'success' => true,
    'message' => 'Quote request received',
    'data' => [
        'name' => $data['name'],
        'phone' => $data['phone'],
        'email' => $data['email'] ?? '',
        'vehicle' => [
            'year' => $data['year'] ?? '',
            'make' => $data['make'] ?? '',
            'model' => $data['model'] ?? '',
            'engine' => $data['engine'] ?? ''
        ],
        'repair' => $data['repair'] ?? '',
        'text_opt_in' => $textOptIn,
        'preferred_slot' => $preferredSlot,
        'estimate' => $estimate_summary,
        'estimate_raw' => $estimate_result,
        'sms' => $sms_result,
        'crm' => $crm_result
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>

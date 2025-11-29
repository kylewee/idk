<?php
// Quote intake endpoint: validates input, calls estimator, and (new) creates a CRM lead
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load CRM config if available
$envFile = __DIR__ . '/.env.local.php';
if (is_file($envFile)) {
    require $envFile;
}

// Load cloud mode logger
$loggerFile = __DIR__ . '/../log/logger.php';
$cloudLogger = null;
if (is_file($loggerFile)) {
    require_once $loggerFile;
    $cloudLogger = new Logger(__DIR__ . '/../log');
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
        'estimate' => $estimate_result
    ]
];

// Log to error_log (will go to PHP error log or syslog)
error_log('QUOTE_INTAKE: ' . json_encode($log_entry));

// Log to cloud mode logger if available
if ($cloudLogger) {
    $cloudLogger->logChat('quote_intake', $log_entry['data']);
}

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
            if (is_array($estimate_result)) {
                $assign('estimate', (string)($estimate_result['total_high'] ?? $estimate_result['total_low'] ?? ''));
            }
            // If a 'notes' field is mapped, include a compact summary
            if (isset($mapf['notes'])) {
                $notes = 'Quote via website' . "\n" .
                         'Phone: ' . ($data['phone'] ?? '') . "\n" .
                         'Email: ' . ($data['email'] ?? '') . "\n" .
                         'Vehicle: ' . ($data['year'] ?? '') . ' ' . ($data['make'] ?? '') . ' ' . ($data['model'] ?? '') . ' ' . ($data['engine'] ?? '') . "\n" .
                         'Repair: ' . ($data['repair'] ?? '') . "\n" .
                         'Estimate: ' . (is_array($estimate_result) ? json_encode($estimate_result) : '');
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
        'estimate' => $estimate_result,
        'crm' => $crm_result
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>

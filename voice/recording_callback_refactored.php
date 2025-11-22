<?php
declare(strict_types=1);

/**
 * Recording Callback Handler (Refactored)
 *
 * Handles Twilio recording callbacks and provides a UI for managing voice call recordings.
 * This is a streamlined version that uses the new service-based architecture.
 */

// Load dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/autoload.php';

use MechanicStAugustine\Voice\TranscriptAnalyzer;
use MechanicStAugustine\Voice\CrmLeadService;
use MechanicStAugustine\Voice\CallLogger;

// Simple logging helper
function voice_log_event(string $event, array $data = []): void
{
    $row = array_merge([
        'ts' => date('c'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ], $data);
    $line = json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    @file_put_contents(__DIR__ . '/voice.log', $line, FILE_APPEND);
}

// Router
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- ROUTE: Download Recording ---
if ($action === 'download') {
    handleDownload();
    exit;
}

// --- ROUTE: Recordings List ---
if ($action === 'recordings') {
    handleRecordingsList();
    exit;
}

// --- ROUTE: Dial (outbound call initiation) ---
if ($action === 'dial') {
    handleDial();
    exit;
}

// --- DEFAULT ROUTE: Twilio Recording Callback ---
handleRecordingCallback();
exit;

// ============================================================================
// ROUTE HANDLERS
// ============================================================================

/**
 * Handle Twilio recording callback (main route)
 */
function handleRecordingCallback(): void
{
    voice_log_event('recording_callback', ['post' => $_POST]);

    // Extract Twilio data
    $recordingSid = $_POST['RecordingSid'] ?? '';
    $callSid = $_POST['CallSid'] ?? '';
    $recordingUrl = $_POST['RecordingUrl'] ?? '';
    $duration = (int)($_POST['RecordingDuration'] ?? 0);
    $from = $_POST['From'] ?? '';
    $to = $_POST['To'] ?? '';

    // Get transcript if available (Twilio native transcription)
    $transcript = $_POST['TranscriptionText'] ?? '';

    if (empty($recordingSid)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing RecordingSid']);
        return;
    }

    // Extract customer data from transcript
    $customerData = [];
    if (!empty($transcript)) {
        $analyzer = new TranscriptAnalyzer();
        $customerData = $analyzer->extractCustomerData($transcript);
    }

    // Log to database
    $logger = new CallLogger();
    $logId = $logger->logCall([
        'call_sid' => $callSid,
        'recording_sid' => $recordingSid,
        'recording_url' => $recordingUrl,
        'transcript' => $transcript,
        'from_number' => $from,
        'to_number' => $to,
        'duration' => $duration,
        'status' => 'completed',
        'customer_data' => $customerData,
    ]);

    // Create CRM lead if we have customer data
    $crmResult = null;
    if (!empty($customerData)) {
        $crmService = new CrmLeadService();
        $crmResult = $crmService->createLead($customerData);

        if ($crmResult['success']) {
            voice_log_event('crm_lead_created', [
                'lead_id' => $crmResult['lead_id'],
                'customer_data' => $customerData,
            ]);
        }
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'log_id' => $logId,
        'crm_lead_id' => $crmResult['lead_id'] ?? null,
        'customer_data' => $customerData,
    ]);
}

/**
 * Handle download recording action
 */
function handleDownload(): void
{
    $recordingSid = $_GET['sid'] ?? '';
    $token = $_GET['token'] ?? '';

    // Verify token
    if (!verifyToken($token)) {
        http_response_code(403);
        echo 'Access denied';
        return;
    }

    if (empty($recordingSid)) {
        http_response_code(400);
        echo 'Missing recording SID';
        return;
    }

    // Proxy the recording from Twilio
    $recordingUrl = sprintf(
        'https://api.twilio.com/2010-04-01/Accounts/%s/Recordings/%s.mp3',
        config('twilio.account_sid'),
        $recordingSid
    );

    $ch = curl_init($recordingUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERPWD => config('twilio.account_sid') . ':' . config('twilio.auth_token'),
    ]);

    $audioData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$audioData) {
        http_response_code(404);
        echo 'Recording not found';
        return;
    }

    header('Content-Type: audio/mpeg');
    header('Content-Disposition: attachment; filename="recording-' . $recordingSid . '.mp3"');
    echo $audioData;
}

/**
 * Handle recordings list page
 */
function handleRecordingsList(): void
{
    $token = $_GET['token'] ?? '';

    // Verify token
    if (!verifyToken($token)) {
        http_response_code(403);
        echo renderAuthForm();
        return;
    }

    // Get recordings from database
    $logger = new CallLogger();
    $recordings = $logger->getAllCalls(100, 0);

    // Render recordings page
    echo renderRecordingsPage($recordings);
}

/**
 * Handle dial action (initiate outbound call)
 */
function handleDial(): void
{
    header('Content-Type: text/xml');

    $to = $_GET['to'] ?? $_POST['to'] ?? '';

    if (empty($to)) {
        echo '<?xml version="1.0" encoding="UTF-8"?><Response><Say>Phone number is required</Say></Response>';
        return;
    }

    // Clean phone number
    $to = preg_replace('/[^0-9\+]/', '', $to);

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Response>';
    echo '<Dial timeout="30" callerId="' . htmlspecialchars(config('twilio.sms_from')) . '">';
    echo '<Number>' . htmlspecialchars($to) . '</Number>';
    echo '</Dial>';
    echo '</Response>';
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Verify access token
 */
function verifyToken(?string $token): bool
{
    $expectedToken = config('voice.recordings_token');

    if (empty($expectedToken)) {
        return true; // No token configured, allow access
    }

    return $token === $expectedToken;
}

/**
 * Render authentication form
 */
function renderAuthForm(): string
{
    return '<!DOCTYPE html>
<html>
<head>
    <title>Access Required</title>
    <style>
        body { font-family: system-ui; max-width: 400px; margin: 100px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; font-size: 16px; }
        button { width: 100%; padding: 10px; font-size: 16px; background: #2563eb; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Access Required</h2>
    <p>Please enter the access token to view recordings.</p>
    <form method="get">
        <input type="hidden" name="action" value="recordings">
        <input type="text" name="token" placeholder="Access Token" required autofocus>
        <button type="submit">Continue</button>
    </form>
</body>
</html>';
}

/**
 * Render recordings page
 */
function renderRecordingsPage(array $recordings): string
{
    $token = $_GET['token'] ?? '';
    $html = '<!DOCTYPE html>
<html>
<head>
    <title>Voice Recordings</title>
    <style>
        body { font-family: system-ui; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f3f4f6; font-weight: 600; }
        .actions a { margin-right: 10px; color: #2563eb; text-decoration: none; }
        .transcript { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Voice Call Recordings</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>From</th>
                <th>Duration</th>
                <th>Transcript</th>
                <th>Customer</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($recordings as $rec) {
        $customerData = json_decode($rec['customer_data'] ?? '{}', true);
        $customerName = $customerData['first_name'] ?? $customerData['name'] ?? '-';

        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($rec['created_at']) . '</td>';
        $html .= '<td>' . htmlspecialchars($rec['from_number']) . '</td>';
        $html .= '<td>' . htmlspecialchars($rec['duration']) . 's</td>';
        $html .= '<td class="transcript">' . htmlspecialchars($rec['transcript'] ?? '-') . '</td>';
        $html .= '<td>' . htmlspecialchars($customerName) . '</td>';
        $html .= '<td class="actions">';
        $html .= '<a href="?action=download&sid=' . urlencode($rec['recording_sid']) . '&token=' . urlencode($token) . '">Download</a>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>
    </table>
</body>
</html>';

    return $html;
}

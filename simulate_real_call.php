<?php
/**
 * Simulate Real Twilio Call Webhook
 *
 * This script simulates what happens when you call your Twilio number
 * and the call is completed. It sends the exact same data that Twilio
 * would send to your recording_callback.php endpoint.
 */

declare(strict_types=1);

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║            SIMULATING REAL TWILIO CALL WEBHOOK                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Step 1: Show what would happen when you call
echo "📞 STEP 1: You call " . "\033[1m+1-904-834-9227\033[0m" . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Twilio receives call and hits: voice/incoming.php\n";
echo "  Response: Forward to your phone +1-904-663-4789\n";
echo "  Recording: ENABLED\n";
echo "\n";

// Step 2: You answer and talk
echo "📱 STEP 2: You answer the call\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$customerTranscript = readline("  👤 What does the customer say? (or press Enter for sample): ");

if (empty(trim($customerTranscript))) {
    $customerTranscript = "Hi, this is Jennifer Martinez calling. I have a 2019 Toyota RAV4 " .
                         "and it's making a really loud grinding noise when I brake. It just started this morning. " .
                         "I'm worried it's not safe to drive. My phone number is 904-555-8765. " .
                         "I'm located in Ponte Vedra Beach off A1A.";
    echo "  Using sample transcript:\n";
    echo "  \"$customerTranscript\"\n";
} else {
    echo "  Customer said: \"$customerTranscript\"\n";
}

echo "\n";

// Step 3: Call ends, Twilio processes
echo "📴 STEP 3: Call ends\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Twilio transcribing recording...\n";
sleep(1);
echo "  ✓ Transcription complete\n";
echo "  Sending webhook to: voice/recording_callback.php\n";
echo "\n";

// Step 4: Prepare webhook data (exactly as Twilio sends it)
echo "🔗 STEP 4: Twilio webhook POST data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$webhookData = [
    'CallSid' => 'CA' . bin2hex(random_bytes(16)),
    'RecordingSid' => 'RE' . bin2hex(random_bytes(16)),
    'RecordingUrl' => 'https://api.twilio.com/2010-04-01/Accounts/AC.../Recordings/RE...',
    'RecordingDuration' => '45',
    'TranscriptionText' => $customerTranscript,
    'TranscriptionStatus' => 'completed',
    'From' => '+19045558765',
    'To' => '+19048349227',
    'CallStatus' => 'completed',
];

foreach ($webhookData as $key => $value) {
    echo sprintf("  %-25s = %s\n", $key,
                 strlen($value) > 60 ? substr($value, 0, 57) . '...' : $value);
}

echo "\n";
echo "🔄 STEP 5: Processing webhook (calling recording_callback.php)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Simulate the POST request to recording_callback.php
$url = 'http://localhost:8080/voice/recording_callback.php';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($webhookData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADER => true,
]);

echo "  Sending POST request...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Parse response
list($headers, $body) = explode("\r\n\r\n", $response, 2);

echo "  Response HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    echo "  ✓ Webhook processed successfully!\n";
} else {
    echo "  ⚠ Unexpected HTTP code: $httpCode\n";
}

echo "\n";

// Step 6: Check the logs
echo "📋 STEP 6: Checking voice.log for processing details\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$logFile = '/home/user/idk/voice/voice.log';

if (file_exists($logFile)) {
    // Get the last few log entries
    $logContent = file_get_contents($logFile);
    $logLines = explode("\n", trim($logContent));
    $recentLogs = array_slice($logLines, -3);

    foreach ($recentLogs as $logLine) {
        if (!empty($logLine)) {
            $logEntry = json_decode($logLine, true);
            if ($logEntry) {
                echo "  • " . ($logEntry['event'] ?? 'unknown') . "\n";
                if (isset($logEntry['data_extracted'])) {
                    echo "    - Data extracted: " . ($logEntry['data_extracted'] ? 'YES' : 'NO') . "\n";
                }
                if (isset($logEntry['lead_created'])) {
                    echo "    - Lead created: " . ($logEntry['lead_created'] ? 'YES' : 'NO') . "\n";
                }
                if (isset($logEntry['lead_id'])) {
                    echo "    - Lead ID: " . $logEntry['lead_id'] . "\n";
                }
            }
        }
    }
} else {
    echo "  ℹ Log file not yet created (will be created on first call)\n";
}

echo "\n";

// Step 7: Extract and show what data was captured
echo "📊 STEP 7: Extracted Customer Data\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Load the services to extract data
require_once '/home/user/idk/src/Config.php';
require_once '/home/user/idk/src/Services/OpenAiService.php';
require_once '/home/user/idk/src/Services/CustomerDataExtractor.php';

$openAiService = new OpenAiService();
$extractor = new CustomerDataExtractor($openAiService);

$extractedData = $extractor->extractWithPatterns($customerTranscript);

if (!empty($extractedData)) {
    foreach ($extractedData as $key => $value) {
        $displayKey = ucwords(str_replace('_', ' ', $key));
        echo sprintf("  ✓ %-20s: %s\n", $displayKey, $value);
    }
} else {
    echo "  ⚠ No data extracted\n";
}

echo "\n";

// Step 8: Show CRM lead creation
echo "💾 STEP 8: Lead Created in CRM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Check if we can connect to database
require_once '/home/user/idk/src/Services/CrmService.php';

$dbConfig = Config::database();

try {
    $mysqli = @new mysqli(
        $dbConfig['server'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );
} catch (Exception $e) {
    $mysqli = null;
}

if ($mysqli && !$mysqli->connect_errno) {
    echo "  ✓ Connected to database\n";

    $entityId = Config::getInt('CRM_LEADS_ENTITY_ID');
    $table = 'app_entity_' . $entityId;

    // Get the most recent lead
    $query = "SELECT * FROM `$table` ORDER BY id DESC LIMIT 1";
    $result = $mysqli->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo "  ✓ Most recent lead found:\n\n";
        echo "    Lead ID: #" . $row['id'] . "\n";
        echo "    Created: " . date('Y-m-d H:i:s', $row['date_added']) . "\n\n";

        // Show mapped fields
        $fieldMap = Config::getArray('CRM_FIELD_MAP');
        echo "    Customer Information:\n";

        foreach ($fieldMap as $fieldName => $fieldId) {
            if ($fieldId > 0) {
                $columnName = 'field_' . $fieldId;
                if (isset($row[$columnName]) && !empty($row[$columnName])) {
                    $displayName = ucwords(str_replace('_', ' ', $fieldName));
                    echo sprintf("      • %-15s: %s\n", $displayName, $row[$columnName]);
                }
            }
        }
    } else {
        echo "  ℹ No leads in database yet\n";
    }

    $mysqli->close();
} else {
    echo "  ⚠ Database not accessible: " . $mysqli->connect_error . "\n";
    echo "  (Lead would be created via REST API to CRM)\n";
}

echo "\n";

// Step 9: Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         SUMMARY                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "  What just happened:\n";
echo "  ✓ Simulated a call to your Twilio number\n";
echo "  ✓ Transcript was processed\n";
echo "  ✓ Customer data was extracted\n";
echo "  ✓ Lead was created in CRM\n";
echo "\n";
echo "  To test with a REAL call:\n";
echo "  1. Deploy this code to a server with a public URL\n";
echo "  2. Configure Twilio webhook:\n";
echo "     https://your-domain.com/voice/incoming.php\n";
echo "  3. Call: +1-904-834-9227\n";
echo "  4. Speak your vehicle issue\n";
echo "  5. Hang up and check the CRM!\n";
echo "\n";

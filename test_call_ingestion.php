<?php
/**
 * Test Call Ingestion Script
 *
 * Simulates a Twilio recording callback to test the complete flow:
 * 1. Load configuration
 * 2. Extract customer data from a sample transcript
 * 3. Create lead in CRM
 */

declare(strict_types=1);

// Set up environment
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== Test Call Ingestion ===" . PHP_EOL;
echo "Testing the complete call-to-CRM pipeline..." . PHP_EOL . PHP_EOL;

// Test 1: Load Configuration
echo "Step 1: Testing configuration loading..." . PHP_EOL;
require_once __DIR__ . '/src/Config.php';

try {
    $config = Config::getInstance();
    echo "✓ Config loaded successfully" . PHP_EOL;

    $twilioConfig = Config::twilio();
    echo "  - Twilio Account SID: " . substr($twilioConfig['account_sid'], 0, 10) . "..." . PHP_EOL;

    $crmConfig = Config::crm();
    echo "  - CRM API URL: " . $crmConfig['api_url'] . PHP_EOL;
    echo "  - CRM Leads Entity ID: " . $crmConfig['leads_entity_id'] . PHP_EOL;

    $dbConfig = Config::database();
    echo "  - Database: " . $dbConfig['database'] . " @ " . $dbConfig['server'] . PHP_EOL;

} catch (Exception $e) {
    echo "✗ Configuration error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// Test 2: Test Services
echo "Step 2: Testing service classes..." . PHP_EOL;

require_once __DIR__ . '/src/Services/OpenAiService.php';
require_once __DIR__ . '/src/Services/CustomerDataExtractor.php';
require_once __DIR__ . '/src/Services/CrmService.php';

$openAiService = new OpenAiService();
$extractor = new CustomerDataExtractor($openAiService);
$crmService = new CrmService();

echo "✓ Services instantiated successfully" . PHP_EOL;
echo PHP_EOL;

// Test 3: Extract customer data from sample transcript
echo "Step 3: Extracting customer data from sample transcript..." . PHP_EOL;

$sampleTranscript = "Hi, my name is Sarah Johnson and I'm calling about my 2018 Honda Accord. " .
                    "The check engine light came on this morning and the car is making a weird noise. " .
                    "My phone number is 904-555-7890. I'm located at 123 Beach Boulevard in St Augustine. " .
                    "Can you come take a look at it today?";

echo "Sample transcript: " . substr($sampleTranscript, 0, 100) . "..." . PHP_EOL;
echo PHP_EOL;

$extractedData = $extractor->extract($sampleTranscript);

if (empty($extractedData)) {
    echo "✗ No data extracted (trying pattern matching directly)..." . PHP_EOL;
    $extractedData = $extractor->extractWithPatterns($sampleTranscript);
}

if (!empty($extractedData)) {
    echo "✓ Data extracted successfully:" . PHP_EOL;
    foreach ($extractedData as $key => $value) {
        echo "  - " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . PHP_EOL;
    }
} else {
    echo "✗ Failed to extract data" . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// Test 4: Validate extracted data
echo "Step 4: Validating extracted data..." . PHP_EOL;
$validatedData = $extractor->validate($extractedData);
echo "✓ Validation complete" . PHP_EOL;
echo PHP_EOL;

// Test 5: Create lead in CRM
echo "Step 5: Creating lead in CRM..." . PHP_EOL;

// Add recording metadata
$leadData = $validatedData;
$leadData['recording_url'] = 'https://api.twilio.com/2010-04-01/Accounts/AC.../Recordings/RE...';
$leadData['transcript'] = $sampleTranscript;
$leadData['_bypass_dedupe'] = true; // For testing, bypass duplicate detection

echo "Attempting to create lead via REST API..." . PHP_EOL;

try {
    $result = $crmService->createLead($leadData);

    if (isset($result['fallback'])) {
        echo "⚠ REST API failed, used DB fallback:" . PHP_EOL;
        $fallbackResult = $result['fallback'];

        if ($fallbackResult['ok'] ?? false) {
            echo "✓ Lead created successfully via DB insert!" . PHP_EOL;
            echo "  - Lead ID: " . ($fallbackResult['id'] ?? 'unknown') . PHP_EOL;

            if ($fallbackResult['duplicate'] ?? false) {
                echo "  - Note: This was a duplicate lead that was updated" . PHP_EOL;
            }
        } else {
            echo "✗ DB insert failed: " . ($fallbackResult['error'] ?? 'unknown error') . PHP_EOL;
            if (isset($fallbackResult['detail'])) {
                echo "  Detail: " . $fallbackResult['detail'] . PHP_EOL;
            }
        }
    } else {
        // Check REST API response
        $httpCode = $result['http'] ?? 0;
        $body = $result['body'] ?? '';

        if ($httpCode >= 200 && $httpCode < 300) {
            echo "✓ Lead created successfully via REST API!" . PHP_EOL;
            echo "  - HTTP Code: " . $httpCode . PHP_EOL;

            $responseData = json_decode($body, true);
            if (isset($responseData['id'])) {
                echo "  - Lead ID: " . $responseData['id'] . PHP_EOL;
            }
        } else {
            echo "⚠ REST API returned non-success status:" . PHP_EOL;
            echo "  - HTTP Code: " . $httpCode . PHP_EOL;
            echo "  - Response: " . substr($body, 0, 200) . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "✗ Exception creating lead: " . $e->getMessage() . PHP_EOL;
    echo "  Stack trace: " . $e->getTraceAsString() . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// Test 6: Verify in database (if DB is accessible)
echo "Step 6: Verifying lead in database..." . PHP_EOL;

try {
    $dbConfig = Config::database();
    $mysqli = @new mysqli(
        $dbConfig['server'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if ($mysqli->connect_errno) {
        echo "⚠ Could not connect to database: " . $mysqli->connect_error . PHP_EOL;
        echo "  (This is expected if database isn't running)" . PHP_EOL;
    } else {
        echo "✓ Connected to database" . PHP_EOL;

        $entityId = $crmConfig['leads_entity_id'];
        $table = 'app_entity_' . $entityId;

        // Get the most recent lead
        $query = "SELECT id, created_by, date_added FROM `$table` ORDER BY id DESC LIMIT 1";
        $result = $mysqli->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            echo "  - Most recent lead ID: " . $row['id'] . PHP_EOL;
            echo "  - Created: " . date('Y-m-d H:i:s', $row['date_added']) . PHP_EOL;

            // Get field values
            $fieldMap = $crmConfig['field_map'];
            if (!empty($fieldMap['first_name'])) {
                $fnField = 'field_' . $fieldMap['first_name'];
                $lnField = 'field_' . $fieldMap['last_name'];
                $phoneField = 'field_' . $fieldMap['phone'];

                $detailQuery = "SELECT `$fnField`, `$lnField`, `$phoneField` FROM `$table` WHERE id = ?";
                $stmt = $mysqli->prepare($detailQuery);
                $leadId = $row['id'];
                $stmt->bind_param('i', $leadId);
                $stmt->execute();
                $detailResult = $stmt->get_result();

                if ($detail = $detailResult->fetch_assoc()) {
                    echo "  - Name: " . ($detail[$fnField] ?? '') . " " . ($detail[$lnField] ?? '') . PHP_EOL;
                    echo "  - Phone: " . ($detail[$phoneField] ?? '') . PHP_EOL;
                }

                $stmt->close();
            }
        } else {
            echo "  - No leads found in database" . PHP_EOL;
        }

        $mysqli->close();
    }

} catch (Exception $e) {
    echo "⚠ Database verification error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "=== Test Complete ===" . PHP_EOL;
echo PHP_EOL;
echo "Summary:" . PHP_EOL;
echo "--------" . PHP_EOL;
echo "If you saw '✓ Lead created successfully', the system is working!" . PHP_EOL;
echo "The transcript was parsed, data extracted, and sent to the CRM." . PHP_EOL;
echo PHP_EOL;
echo "Next steps to test with a real call:" . PHP_EOL;
echo "1. Make sure the production domain is accessible" . PHP_EOL;
echo "2. Configure Twilio webhook to point to: https://mechanicstaugustine.com/voice/incoming.php" . PHP_EOL;
echo "3. Call your Twilio number" . PHP_EOL;
echo "4. Check voice/voice.log for processing details" . PHP_EOL;

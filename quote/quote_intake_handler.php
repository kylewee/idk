<?php
/**
 * Quote Intake Handler for MechanicSaintAugustine.com
 * Validates leads, runs estimator, logs results, integrates with CRM, and sends SMS quotes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
require_once __DIR__ . '/../api/.env.local.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate and sanitize input
    $leadData = validateAndSanitizeInput($_POST);
    
    // Run internal estimator
    $estimate = runEstimator($leadData);
    
    // Log the lead and estimate
    $logId = logLead($leadData, $estimate);
    
    // Push to Rukovoditel CRM
    $crmResult = pushToRukovoditel($leadData, $estimate, $logId);
    
    // Send SMS quote if opted in
    $smsResult = null;
    if ($leadData['sms_opt_in']) {
        $smsResult = sendTwilioQuote($leadData, $estimate);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => $leadData['sms_opt_in'] 
            ? 'Quote submitted successfully! You will receive an SMS with your estimate shortly.'
            : 'Quote submitted successfully! We will contact you soon with your estimate.',
        'estimate' => $estimate,
        'log_id' => $logId
    ]);
    
} catch (Exception $e) {
    error_log("Quote intake error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred processing your request. Please try again.'
    ]);
}

function validateAndSanitizeInput($input) {
    $required_fields = ['name', 'phone', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'service_type', 'preferred_time'];
    
    $leadData = [];
    
    // Check required fields
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
        $leadData[$field] = sanitizeInput($input[$field]);
    }
    
    // Optional fields
    $leadData['email'] = !empty($input['email']) ? sanitizeInput($input['email']) : '';
    $leadData['description'] = !empty($input['description']) ? sanitizeInput($input['description']) : '';
    $leadData['sms_opt_in'] = !empty($input['sms_opt_in']) && $input['sms_opt_in'] == '1';
    
    // Validate phone number
    $phone = preg_replace('/[^\d]/', '', $leadData['phone']);
    if (strlen($phone) !== 10) {
        throw new Exception("Invalid phone number format");
    }
    $leadData['phone_clean'] = $phone;
    
    // Validate email if provided
    if ($leadData['email'] && !filter_var($leadData['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Validate vehicle year
    $currentYear = date('Y');
    if ($leadData['vehicle_year'] < 1990 || $leadData['vehicle_year'] > $currentYear) {
        throw new Exception("Invalid vehicle year");
    }
    
    // Validate preferred time
    $preferredTime = DateTime::createFromFormat(DateTime::ISO8601, $leadData['preferred_time']);
    if (!$preferredTime) {
        throw new Exception("Invalid preferred time format");
    }
    
    $leadData['created_at'] = date('Y-m-d H:i:s');
    $leadData['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    return $leadData;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function runEstimator($leadData) {
    // Internal estimator logic based on service type and vehicle info
    $basePrices = [
        'oil_change' => ['min' => 35, 'max' => 65],
        'brake_repair' => ['min' => 150, 'max' => 400],
        'engine_diagnostics' => ['min' => 90, 'max' => 150],
        'transmission' => ['min' => 200, 'max' => 800],
        'other' => ['min' => 75, 'max' => 300]
    ];
    
    $serviceType = $leadData['service_type'];
    $vehicleYear = intval($leadData['vehicle_year']);
    
    // Get base price range
    $priceRange = $basePrices[$serviceType] ?? $basePrices['other'];
    
    // Adjust for vehicle age (older vehicles may cost more)
    $currentYear = date('Y');
    $vehicleAge = $currentYear - $vehicleYear;
    
    if ($vehicleAge > 15) {
        $priceRange['min'] *= 1.2;
        $priceRange['max'] *= 1.3;
    } elseif ($vehicleAge > 10) {
        $priceRange['min'] *= 1.1;
        $priceRange['max'] *= 1.2;
    }
    
    // Round to nearest $5
    $estimateMin = round($priceRange['min'] / 5) * 5;
    $estimateMax = round($priceRange['max'] / 5) * 5;
    
    return [
        'service_type' => $serviceType,
        'estimate_min' => $estimateMin,
        'estimate_max' => $estimateMax,
        'estimate_range' => "$" . number_format($estimateMin) . " - $" . number_format($estimateMax),
        'notes' => generateEstimateNotes($leadData, $vehicleAge)
    ];
}

function generateEstimateNotes($leadData, $vehicleAge) {
    $notes = [];
    
    if ($vehicleAge > 15) {
        $notes[] = "Older vehicle may require additional parts/labor";
    }
    
    if ($leadData['description']) {
        $notes[] = "Customer description: " . $leadData['description'];
    }
    
    $notes[] = "Final price determined after inspection";
    
    return implode(". ", $notes);
}

function logLead($leadData, $estimate) {
    global $DB_CONFIG;
    
    try {
        $pdo = new PDO(
            "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['database']};charset=utf8mb4",
            $DB_CONFIG['username'],
            $DB_CONFIG['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $sql = "INSERT INTO quote_leads (
            name, phone, phone_clean, email, vehicle_year, vehicle_make, vehicle_model,
            service_type, description, preferred_time, sms_opt_in, estimate_min, estimate_max,
            estimate_notes, created_at, ip_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $leadData['name'],
            $leadData['phone'],
            $leadData['phone_clean'],
            $leadData['email'],
            $leadData['vehicle_year'],
            $leadData['vehicle_make'],
            $leadData['vehicle_model'],
            $leadData['service_type'],
            $leadData['description'],
            $leadData['preferred_time'],
            $leadData['sms_opt_in'] ? 1 : 0,
            $estimate['estimate_min'],
            $estimate['estimate_max'],
            $estimate['notes'],
            $leadData['created_at'],
            $leadData['ip_address']
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Failed to save lead data");
    }
}

function pushToRukovoditel($leadData, $estimate, $logId) {
    global $RUKOVODITEL_CONFIG;
    
    if (!$RUKOVODITEL_CONFIG['enabled']) {
        return ['success' => false, 'message' => 'CRM integration disabled'];
    }
    
    $crmData = [
        'name' => $leadData['name'],
        'phone' => $leadData['phone'],
        'email' => $leadData['email'],
        'vehicle' => $leadData['vehicle_year'] . ' ' . $leadData['vehicle_make'] . ' ' . $leadData['vehicle_model'],
        'service_type' => $leadData['service_type'],
        'description' => $leadData['description'],
        'estimate_range' => $estimate['estimate_range'],
        'preferred_time' => $leadData['preferred_time'],
        'source' => 'Website Quote Form',
        'log_id' => $logId
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $RUKOVODITEL_CONFIG['api_url'] . '/api/leads',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($crmData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $RUKOVODITEL_CONFIG['api_key']
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        return ['success' => true, 'response' => json_decode($response, true)];
    } else {
        error_log("Rukovoditel API error: HTTP $httpCode - $response");
        return ['success' => false, 'message' => 'CRM integration failed'];
    }
}

function sendTwilioQuote($leadData, $estimate) {
    global $TWILIO_CONFIG;
    
    if (!$TWILIO_CONFIG['enabled']) {
        return ['success' => false, 'message' => 'SMS integration disabled'];
    }
    
    $message = "Hi {$leadData['name']}! Your auto repair estimate for {$leadData['vehicle_year']} {$leadData['vehicle_make']} {$leadData['vehicle_model']} ({$leadData['service_type']}): {$estimate['estimate_range']}. We'll call to schedule your appointment. - Mechanic Saint Augustine";
    
    $data = [
        'To' => '+1' . $leadData['phone_clean'],
        'Body' => $message
    ];
    
    // Use messaging service if configured, otherwise use phone number
    if (!empty($TWILIO_CONFIG['messaging_service_sid'])) {
        $data['MessagingServiceSid'] = $TWILIO_CONFIG['messaging_service_sid'];
    } else {
        $data['From'] = $TWILIO_CONFIG['phone_number'];
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/' . $TWILIO_CONFIG['account_sid'] . '/Messages.json',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode($TWILIO_CONFIG['account_sid'] . ':' . $TWILIO_CONFIG['auth_token'])
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        return ['success' => true, 'response' => json_decode($response, true)];
    } else {
        error_log("Twilio API error: HTTP $httpCode - $response");
        return ['success' => false, 'message' => 'SMS sending failed'];
    }
}
?>
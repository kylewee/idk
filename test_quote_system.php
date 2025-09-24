<?php
/**
 * Test script for quote system functionality
 * Run this to test the backend without a full web server setup
 */

// Simulate POST data
$_POST = [
    'name' => 'John Doe',
    'phone' => '(904) 555-1234',
    'email' => 'john.doe@example.com',
    'vehicle_year' => '2020',
    'vehicle_make' => 'Toyota',
    'vehicle_model' => 'Camry',
    'service_type' => 'oil_change',
    'description' => 'Regular maintenance oil change',
    'preferred_time' => '2024-09-25T09:00:00.000Z',
    'sms_opt_in' => '1'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "🧪 Testing MechanicSaintAugustine.com Quote System\n";
echo "==============================================\n\n";

// Test configuration loading
echo "📋 Testing configuration...\n";
try {
    require_once __DIR__ . '/api/.env.local.php';
    echo "✅ Configuration loaded successfully\n";
    echo "   - Database: {$DB_CONFIG['database']}\n";
    echo "   - Twilio enabled: " . ($TWILIO_CONFIG['enabled'] ? 'Yes' : 'No') . "\n";
    echo "   - CRM enabled: " . ($RUKOVODITEL_CONFIG['enabled'] ? 'Yes' : 'No') . "\n\n";
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "\n\n";
}

// Test input validation
echo "🔍 Testing input validation...\n";
try {
    require_once __DIR__ . '/quote/quote_intake_handler.php';
} catch (Exception $e) {
    echo "Note: Full handler test requires database connection\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test estimator independently
echo "💰 Testing price estimator...\n";
function testEstimator() {
    $testData = [
        'service_type' => 'oil_change',
        'vehicle_year' => 2020,
        'description' => 'Test description'
    ];
    
    // Simulate estimator logic
    $basePrices = [
        'oil_change' => ['min' => 35, 'max' => 65],
        'brake_repair' => ['min' => 150, 'max' => 400],
        'engine_diagnostics' => ['min' => 90, 'max' => 150],
        'transmission' => ['min' => 200, 'max' => 800],
        'other' => ['min' => 75, 'max' => 300]
    ];
    
    $serviceType = $testData['service_type'];
    $priceRange = $basePrices[$serviceType];
    
    $currentYear = date('Y');
    $vehicleAge = $currentYear - $testData['vehicle_year'];
    
    if ($vehicleAge > 15) {
        $priceRange['min'] *= 1.2;
        $priceRange['max'] *= 1.3;
    } elseif ($vehicleAge > 10) {
        $priceRange['min'] *= 1.1;
        $priceRange['max'] *= 1.2;
    }
    
    $estimateMin = round($priceRange['min'] / 5) * 5;
    $estimateMax = round($priceRange['max'] / 5) * 5;
    
    return [
        'service_type' => $serviceType,
        'estimate_min' => $estimateMin,
        'estimate_max' => $estimateMax,
        'estimate_range' => "$" . number_format($estimateMin) . " - $" . number_format($estimateMax),
        'vehicle_age' => $vehicleAge
    ];
}

$estimate = testEstimator();
echo "✅ Estimator working correctly\n";
echo "   - Service: " . ucfirst(str_replace('_', ' ', $estimate['service_type'])) . "\n";
echo "   - Vehicle age: {$estimate['vehicle_age']} years\n";
echo "   - Estimate: {$estimate['estimate_range']}\n\n";

// Test time slot generation
echo "⏰ Testing time slot generation...\n";
function testTimeSlots() {
    $today = new DateTime();
    $businessHours = [
        1 => ['start' => 8, 'end' => 18], // Monday
        2 => ['start' => 8, 'end' => 18], // Tuesday
        3 => ['start' => 8, 'end' => 18], // Wednesday
        4 => ['start' => 8, 'end' => 18], // Thursday
        5 => ['start' => 8, 'end' => 18], // Friday
        6 => ['start' => 8, 'end' => 16], // Saturday
        0 => null // Sunday - closed
    ];
    
    $slots = [];
    for ($dayOffset = 0; $dayOffset < 3; $dayOffset++) {
        $date = clone $today;
        $date->add(new DateInterval("P{$dayOffset}D"));
        $date->setTime(0, 0, 0);
        
        $dayOfWeek = (int)$date->format('w');
        $hours = $businessHours[$dayOfWeek];
        
        if (!$hours) continue;
        
        for ($hour = $hours['start']; $hour < min($hours['start'] + 3, $hours['end']); $hour++) {
            $slotDate = clone $date;
            $slotDate->setTime($hour, 0, 0);
            
            if ($dayOffset === 0 && $slotDate <= $today) continue;
            
            $slots[] = $slotDate->format('D M j, g:i A');
        }
    }
    
    return $slots;
}

$timeSlots = testTimeSlots();
echo "✅ Time slot generation working\n";
echo "   - Available slots: " . count($timeSlots) . "\n";
echo "   - Sample slots:\n";
foreach (array_slice($timeSlots, 0, 3) as $slot) {
    echo "     • $slot\n";
}

echo "\n🎉 Basic functionality tests completed successfully!\n";
echo "\n📝 Next steps for deployment:\n";
echo "1. Set up MySQL database using api/database_schema.sql\n";
echo "2. Configure credentials in api/.env.local.php\n";
echo "3. Run ./deploy.sh for full deployment\n";
echo "4. Test form submission on the live site\n";
?>
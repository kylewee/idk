<?php
/**
 * Configuration file for MechanicSaintAugustine.com
 * Contains database, CRM, and SMS service settings
 */

// Database Configuration
$DB_CONFIG = [
    'host' => 'localhost',
    'database' => 'mechanic_sa',
    'username' => 'mechanic_user',
    'password' => 'secure_password_here',
    'charset' => 'utf8mb4'
];

// Rukovoditel CRM Configuration
$RUKOVODITEL_CONFIG = [
    'enabled' => true,
    'api_url' => 'https://your-rukovoditel-instance.com',
    'api_key' => 'your_rukovoditel_api_key_here',
    'timeout' => 30
];

// Twilio SMS Configuration
$TWILIO_CONFIG = [
    'enabled' => true,
    'account_sid' => 'your_twilio_account_sid_here',
    'auth_token' => 'your_twilio_auth_token_here',
    // Use either phone_number OR messaging_service_sid
    'phone_number' => '+1234567890', // Your Twilio phone number
    'messaging_service_sid' => '', // Optional: Twilio Messaging Service SID
    'timeout' => 30
];

// Business Configuration
$BUSINESS_CONFIG = [
    'name' => 'Mechanic Saint Augustine',
    'phone' => '(904) 555-0123',
    'email' => 'info@mechanicsaintaugustine.com',
    'address' => [
        'street' => '123 Main Street',
        'city' => 'Saint Augustine',
        'state' => 'FL',
        'zip' => '32084'
    ],
    'hours' => [
        'monday' => ['start' => '08:00', 'end' => '18:00'],
        'tuesday' => ['start' => '08:00', 'end' => '18:00'],
        'wednesday' => ['start' => '08:00', 'end' => '18:00'],
        'thursday' => ['start' => '08:00', 'end' => '18:00'],
        'friday' => ['start' => '08:00', 'end' => '18:00'],
        'saturday' => ['start' => '08:00', 'end' => '16:00'],
        'sunday' => 'closed'
    ]
];

// Application Settings
$APP_CONFIG = [
    'debug' => false,
    'log_file' => __DIR__ . '/../logs/app.log',
    'max_quote_age_days' => 30,
    'default_timezone' => 'America/New_York'
];

// Set timezone
date_default_timezone_set($APP_CONFIG['default_timezone']);

// Error reporting based on debug mode
if ($APP_CONFIG['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $APP_CONFIG['log_file']);
}

// Create logs directory if it doesn't exist
$logDir = dirname($APP_CONFIG['log_file']);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
?>
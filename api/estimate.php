<?php
/**
 * Estimate API Endpoint
 *
 * Provides pricing estimates using the centralized PricingService.
 * This replaces the external estimate API call at http://127.0.0.1:8091/api/estimate
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Load dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/PricingService.php';

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Accept both GET and POST
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: [];
} else {
    $data = $_GET;
}

// Get repair type from request
$repair = $data['repair'] ?? $data['service'] ?? '';

if (empty($repair)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameter: repair or service',
    ]);
    exit;
}

// Get vehicle details
$vehicle = [
    'year' => $data['year'] ?? null,
    'make' => $data['make'] ?? null,
    'model' => $data['model'] ?? null,
    'engine_size' => $data['engine'] ?? $data['engine_size'] ?? null,
];

// Calculate estimate
try {
    $pricing = PricingService::getInstance();
    $estimate = $pricing->getEstimateResponse($repair, $vehicle);

    http_response_code($estimate['success'] ? 200 : 404);
    echo json_encode($estimate, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage(),
    ]);
}

<?php

namespace MechanicStAugustine\Voice;

/**
 * CRM Lead Service
 *
 * Handles creation of leads in the Rukovoditel CRM system from voice call data.
 */
class CrmLeadService
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'api_url' => config('crm.api_url'),
            'api_key' => config('crm.api_key'),
            'username' => config('crm.username'),
            'password' => config('crm.password'),
            'leads_entity_id' => config('crm.leads_entity_id'),
            'created_by_user_id' => config('crm.created_by_user_id'),
            'field_map' => config('crm.field_map', []),
        ];
    }

    /**
     * Create a lead in the CRM from call data
     *
     * @param array $callData Call data with customer information
     * @return array Result of the CRM operation
     */
    public function createLead(array $callData): array
    {
        if (empty($this->config['api_url']) || empty($this->config['leads_entity_id'])) {
            return [
                'success' => false,
                'error' => 'CRM not configured',
            ];
        }

        // Split name into first and last
        $name = trim($callData['name'] ?? '');
        $firstName = $name;
        $lastName = '';

        if (strpos($name, ' ') !== false) {
            $parts = preg_split('/\s+/', $name);
            $lastName = array_pop($parts);
            $firstName = trim(implode(' ', $parts)) ?: $name;
        }

        // Build field values based on mapping
        $fieldValues = [];
        $this->assignField($fieldValues, 'first_name', $callData['first_name'] ?? $firstName);
        $this->assignField($fieldValues, 'last_name', $callData['last_name'] ?? $lastName ?: $firstName);
        $this->assignField($fieldValues, 'name', $name);
        $this->assignField($fieldValues, 'phone', $callData['phone'] ?? '');
        $this->assignField($fieldValues, 'address', $callData['address'] ?? '');
        $this->assignField($fieldValues, 'year', $callData['year'] ?? '');
        $this->assignField($fieldValues, 'make', $callData['make'] ?? '');
        $this->assignField($fieldValues, 'model', $callData['model'] ?? '');
        $this->assignField($fieldValues, 'engine_size', $callData['engine'] ?? '');
        $this->assignField($fieldValues, 'notes', $callData['notes'] ?? '');

        if (empty($fieldValues)) {
            return [
                'success' => false,
                'error' => 'No valid fields to create lead',
            ];
        }

        // Make API request to create lead
        $payload = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'path' => 'items/entities_' . $this->config['leads_entity_id'],
            'method' => 'POST',
            'data' => [
                'fields' => $fieldValues,
                'created_by' => $this->config['created_by_user_id'],
            ],
        ];

        $ch = curl_init($this->config['api_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'API-KEY: ' . $this->config['api_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            error_log("CRM_LEAD: API error - HTTP $httpCode: $error");
            return [
                'success' => false,
                'error' => "HTTP $httpCode: $error",
                'http_code' => $httpCode,
            ];
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
            error_log("CRM_LEAD: Invalid response: " . substr($response, 0, 200));
            return [
                'success' => false,
                'error' => 'Invalid CRM response',
                'response' => $result,
            ];
        }

        return [
            'success' => true,
            'lead_id' => $result['data']['id'] ?? null,
            'response' => $result,
        ];
    }

    /**
     * Assign a field value to the field values array using the field map
     *
     * @param array &$fieldValues Field values array (modified by reference)
     * @param string $key Field key from mapping
     * @param mixed $value Field value
     */
    private function assignField(array &$fieldValues, string $key, $value): void
    {
        if (!isset($this->config['field_map'][$key])) {
            return;
        }

        $fieldId = (int)$this->config['field_map'][$key];

        if ($fieldId <= 0) {
            return;
        }

        if ($value === '' || $value === null) {
            return;
        }

        $fieldValues[$fieldId] = (string)$value;
    }
}

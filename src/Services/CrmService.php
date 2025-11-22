<?php

declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

/**
 * CRM Service
 *
 * Handles all interactions with the Rukovoditel CRM system
 * Provides methods for lead creation, authentication, field mapping, and database operations
 *
 * @package MechanicStAugustine\Services
 */
class CrmService
{
    private Config $config;
    private ?array $fieldMap = null;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? Config::getInstance();
    }

    /**
     * Create a new lead in the CRM
     *
     * @param array $leadData Lead information (name, phone, vehicle details, etc.)
     * @return array Result of the operation
     */
    public function createLead(array $leadData): array
    {
        $crmConfig = $this->config::crm();
        $post = [
            'action' => 'insert',
            'entity_id' => $crmConfig['leads_entity_id'],
        ];

        // Attempt API token-based authentication first
        if (!empty($crmConfig['username']) && !empty($crmConfig['password'])) {
            $token = $this->authenticate(
                $crmConfig['username'],
                $crmConfig['password'],
                $crmConfig['api_url']
            );

            if ($token) {
                $post['token'] = $token;
            } elseif (!empty($crmConfig['api_key'])) {
                $post['key'] = $crmConfig['api_key'];
            }
        } elseif (!empty($crmConfig['api_key'])) {
            $post['key'] = $crmConfig['api_key'];
        }

        // Synthesize full name if needed
        $leadData = $this->synthesizeName($leadData);

        // Synthesize notes if missing
        $leadData = $this->synthesizeNotes($leadData);

        // Map fields to CRM field IDs
        $fieldMap = $this->resolveFieldMap();
        if (is_array($fieldMap)) {
            foreach ($fieldMap as $key => $fieldId) {
                $fieldId = (int)$fieldId;
                if ($fieldId <= 0) continue;
                if (!array_key_exists($key, $leadData)) continue;

                $value = (string)$leadData[$key];
                if ($value === '') continue;

                $post['fields[field_' . $fieldId . ']'] = $value;
            }
        }

        // Try REST API
        $apiUrl = $crmConfig['api_url'];
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = [
            'http' => $httpCode,
            'curl_errno' => $errno,
            'curl_error' => $error,
            'body' => $response
        ];

        // Fallback to direct DB insert if API fails
        $shouldFallback = ($errno !== 0) || ($httpCode >= 500) || ($response === false);
        if ($shouldFallback) {
            $dbResult = $this->createLeadDbInsert($leadData);
            $result['fallback'] = $dbResult;
        }

        return $result;
    }

    /**
     * Authenticate with CRM API and get token
     *
     * @param string $username CRM username
     * @param string $password CRM password
     * @param string $apiUrl CRM API URL
     * @return string|null Authentication token or null on failure
     */
    private function authenticate(string $username, string $password, string $apiUrl): ?string
    {
        $loginPost = [
            'action' => 'login',
            'username' => $username,
            'password' => $password,
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $loginPost,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode((string)$response, true);
        return is_array($data) && !empty($data['token']) ? (string)$data['token'] : null;
    }

    /**
     * Create lead via direct database insert (fallback method)
     *
     * @param array $leadData Lead information
     * @return array Result of the operation
     */
    public function createLeadDbInsert(array $leadData): array
    {
        $crmConfig = $this->config::crm();
        $entityId = $crmConfig['leads_entity_id'];

        if ($entityId <= 0) {
            return ['ok' => false, 'error' => 'leads_entity_id_missing'];
        }

        try {
            $dbConfig = $this->getDatabaseConfig();
            if (!$dbConfig) {
                return ['ok' => false, 'error' => 'db_config_missing'];
            }

            $mysqli = @new mysqli(
                $dbConfig['server'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['database']
            );

            if ($mysqli->connect_errno) {
                return [
                    'ok' => false,
                    'error' => 'db_connect',
                    'detail' => $mysqli->connect_error
                ];
            }

            $table = 'app_entity_' . $entityId;

            // Check for duplicates before inserting
            $duplicate = $this->checkDuplicate($mysqli, $table, $leadData);
            if ($duplicate) {
                $mysqli->close();
                return $duplicate;
            }

            // Build insert query
            $columns = [];
            $values = [];
            $types = '';
            $seenCols = [];

            // Base columns
            $createdBy = $crmConfig['created_by_user_id'];
            $dateAdded = time();

            foreach ([
                'created_by' => $createdBy,
                'date_added' => $dateAdded,
                'parent_item_id' => 0,
                'sort_order' => 0,
            ] as $col => $val) {
                $columns[] = "`$col`";
                $values[] = $val;
                $types .= 'i';
                $seenCols[$col] = true;
            }

            // Synthesize name
            $leadData = $this->synthesizeName($leadData);

            // Map fields
            $fieldMap = $this->resolveFieldMap();
            if (is_array($fieldMap)) {
                foreach ($fieldMap as $key => $fieldId) {
                    $fieldId = (int)$fieldId;
                    if ($fieldId <= 0 || !array_key_exists($key, $leadData)) continue;

                    $value = (string)$leadData[$key];

                    // Normalize phone format
                    if ($key === 'phone') {
                        $value = preg_replace('/[^\d\+]/', '', $value);
                    }

                    if ($value === '') continue;

                    $col = 'field_' . $fieldId;
                    if (isset($seenCols[$col])) continue;

                    $seenCols[$col] = true;
                    $columns[] = '`' . $col . '`';
                    $values[] = $value;
                    $types .= 's';
                }
            }

            // Add notes if mapped
            $leadData = $this->synthesizeNotes($leadData);
            if (!empty($leadData['notes']) && isset($fieldMap['notes'])) {
                $notesFieldId = (int)$fieldMap['notes'];
                $notesCol = 'field_' . $notesFieldId;

                if ($notesFieldId > 0 && !isset($seenCols[$notesCol])) {
                    $columns[] = '`' . $notesCol . '`';
                    $values[] = $leadData['notes'];
                    $types .= 's';
                    $seenCols[$notesCol] = true;
                }
            }

            // Handle NOT NULL columns without defaults
            $this->fillRequiredColumns($mysqli, $table, $columns, $values, $types, $seenCols);

            // Execute insert
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $sql = 'INSERT INTO `' . $mysqli->real_escape_string($table) . '` ('
                . implode(',', $columns) . ') VALUES (' . $placeholders . ')';

            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                $mysqli->close();
                return [
                    'ok' => false,
                    'error' => 'prepare',
                    'detail' => $mysqli->error,
                    'sql' => $sql
                ];
            }

            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                $mysqli->close();
                return [
                    'ok' => false,
                    'error' => 'execute',
                    'detail' => $error,
                    'sql' => $sql
                ];
            }

            $id = $stmt->insert_id;
            $stmt->close();
            $mysqli->close();

            return ['ok' => true, 'id' => $id];

        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => 'exception',
                'detail' => $e->getMessage()
            ];
        }
    }

    /**
     * Check for duplicate leads based on phone number
     *
     * @param mysqli $mysqli Database connection
     * @param string $table Table name
     * @param array $leadData Lead data
     * @return array|null Duplicate result or null if no duplicate
     */
    private function checkDuplicate($mysqli, string $table, array &$leadData): ?array
    {
        // Skip deduplication if bypass flag is set
        if (!empty($leadData['_bypass_dedupe'])) {
            return null;
        }

        $fieldMap = $this->resolveFieldMap();
        $phoneFieldId = isset($fieldMap['phone']) ? (int)$fieldMap['phone'] : 0;

        if ($phoneFieldId <= 0 || empty($leadData['phone'])) {
            return null;
        }

        // Normalize phone number
        $phoneNorm = preg_replace('/[^\d\+]/', '', $leadData['phone']);
        if ($phoneNorm === '') {
            return null;
        }

        $phoneCol = 'field_' . $phoneFieldId;
        $oneHourAgo = time() - 3600;

        // Check for existing lead with same phone in last hour
        $query = 'SELECT `id` FROM `' . $mysqli->real_escape_string($table)
            . '` WHERE `' . $phoneCol . '` = ? AND `date_added` > ? ORDER BY `id` DESC LIMIT 1';

        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('si', $phoneNorm, $oneHourAgo);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        if (!$result || !($row = $result->fetch_assoc())) {
            $stmt->close();
            return null;
        }

        $existingId = (int)$row['id'];
        $stmt->close();

        // Update empty fields on existing lead
        $updated = $this->updateEmptyFields($mysqli, $table, $existingId, $leadData);

        return [
            'ok' => true,
            'id' => $existingId,
            'duplicate' => true,
            'updated' => $updated
        ];
    }

    /**
     * Update empty fields on an existing lead
     *
     * @param mysqli $mysqli Database connection
     * @param string $table Table name
     * @param int $existingId Existing lead ID
     * @param array $leadData New lead data
     * @return bool True if fields were updated
     */
    private function updateEmptyFields($mysqli, string $table, int $existingId, array $leadData): bool
    {
        $fieldMap = $this->resolveFieldMap();
        $updateCols = [];
        $updateVals = [];
        $updateTypes = '';

        // Fetch current field values
        $colsToCheck = [];
        foreach ($fieldMap as $key => $fieldId) {
            $fieldId = (int)$fieldId;
            if ($fieldId <= 0) continue;
            $col = 'field_' . $fieldId;
            $colsToCheck[$key] = $col;
        }

        if (empty($colsToCheck)) {
            return false;
        }

        $selCols = array_values($colsToCheck);
        $selSql = 'SELECT `' . implode('`,`', $selCols) . '` FROM `'
            . $mysqli->real_escape_string($table) . '` WHERE `id` = ? LIMIT 1';

        $sel = $mysqli->prepare($selSql);
        if (!$sel) {
            return false;
        }

        $sel->bind_param('i', $existingId);
        if (!$sel->execute()) {
            $sel->close();
            return false;
        }

        $er = $sel->get_result();
        $current = $er ? $er->fetch_assoc() : null;
        $sel->close();

        if (!is_array($current)) {
            return false;
        }

        // Build update query for empty fields
        foreach ($fieldMap as $key => $fieldId) {
            $fieldId = (int)$fieldId;
            if ($fieldId <= 0) continue;
            if (!array_key_exists($key, $leadData)) continue;

            $value = (string)$leadData[$key];
            if ($key === 'phone') {
                $value = preg_replace('/[^\d\+]/', '', $value);
            }
            if ($value === '') continue;

            $col = 'field_' . $fieldId;
            $currentValue = (string)($current[$col] ?? '');

            // Only update if current value is empty
            if ($currentValue === '' || $currentValue === '0') {
                $updateCols[] = '`' . $col . '` = ?';
                $updateVals[] = $value;
                $updateTypes .= 's';
            }
        }

        if (empty($updateCols)) {
            return false;
        }

        // Add date_updated if column exists
        $hasDateUpdated = false;
        $res = $mysqli->query('SHOW COLUMNS FROM `' . $mysqli->real_escape_string($table) . "` LIKE 'date_updated'");
        if ($res) {
            $hasDateUpdated = (bool)$res->num_rows;
            $res->free();
        }

        if ($hasDateUpdated) {
            $updateCols[] = '`date_updated` = ?';
            $updateVals[] = time();
            $updateTypes .= 'i';
        }

        // Execute update
        $updSql = 'UPDATE `' . $mysqli->real_escape_string($table) . '` SET '
            . implode(',', $updateCols) . ' WHERE `id` = ?';

        $upd = $mysqli->prepare($updSql);
        if (!$upd) {
            return false;
        }

        $updateTypes .= 'i';
        $updateVals[] = $existingId;
        $upd->bind_param($updateTypes, ...$updateVals);
        $result = $upd->execute();
        $upd->close();

        return $result;
    }

    /**
     * Fill required NOT NULL columns with safe default values
     *
     * @param mysqli $mysqli Database connection
     * @param string $table Table name
     * @param array $columns Column names (passed by reference)
     * @param array $values Column values (passed by reference)
     * @param string $types Type string (passed by reference)
     * @param array $seenCols Already processed columns
     */
    private function fillRequiredColumns($mysqli, string $table, array &$columns, array &$values, string &$types, array &$seenCols): void
    {
        $colInfo = $mysqli->query('SHOW COLUMNS FROM `' . $mysqli->real_escape_string($table) . '`');
        if (!$colInfo) {
            return;
        }

        while ($col = $colInfo->fetch_assoc()) {
            $colName = $col['Field'] ?? '';

            // Only process field_* columns
            if (strpos($colName, 'field_') !== 0) continue;
            if (isset($seenCols[$colName])) continue;

            $nullable = strtolower((string)($col['Null'] ?? ''));
            $default = $col['Default'] ?? null;

            // Only fill NOT NULL columns without defaults
            if ($nullable === 'no' && $default === null) {
                $colType = strtolower((string)($col['Type'] ?? ''));

                // Determine safe fallback value
                $isNumeric = (
                    strpos($colType, 'int') !== false ||
                    strpos($colType, 'decimal') !== false ||
                    strpos($colType, 'float') !== false ||
                    strpos($colType, 'double') !== false
                );

                $fallbackVal = $isNumeric ? 0 : '';

                $columns[] = '`' . $colName . '`';
                $values[] = $fallbackVal;
                $types .= $isNumeric ? 'i' : 's';
                $seenCols[$colName] = true;
            }
        }

        $colInfo->close();
    }

    /**
     * Resolve field mapping (from config or auto-discovery)
     *
     * @return array Field mapping array
     */
    private function resolveFieldMap(): array
    {
        if ($this->fieldMap !== null) {
            return $this->fieldMap;
        }

        $crmConfig = $this->config::crm();
        $this->fieldMap = $crmConfig['field_map'] ?? [];

        return $this->fieldMap;
    }

    /**
     * Get database configuration
     *
     * @return array|null Database configuration or null if not available
     */
    private function getDatabaseConfig(): ?array
    {
        $dbConfig = $this->config::database();

        if (empty($dbConfig['server']) || empty($dbConfig['username']) || empty($dbConfig['database'])) {
            return null;
        }

        return $dbConfig;
    }

    /**
     * Synthesize full name from first/last name if needed
     *
     * @param array $leadData Lead data
     * @return array Updated lead data
     */
    private function synthesizeName(array $leadData): array
    {
        if (empty($leadData['name'])) {
            $firstName = trim((string)($leadData['first_name'] ?? ''));
            $lastName = trim((string)($leadData['last_name'] ?? ''));

            if ($firstName || $lastName) {
                $leadData['name'] = trim($firstName . ' ' . $lastName);
            }
        }

        return $leadData;
    }

    /**
     * Synthesize notes from recording/transcript if needed
     *
     * @param array $leadData Lead data
     * @return array Updated lead data
     */
    private function synthesizeNotes(array $leadData): array
    {
        $fieldMap = $this->resolveFieldMap();

        if (isset($fieldMap['notes']) && !isset($leadData['notes'])) {
            $parts = [];

            if (!empty($leadData['recording_url'])) {
                $parts[] = "Recording: " . $leadData['recording_url'];
            }

            if (!empty($leadData['transcript'])) {
                $parts[] = "Transcript: " . $leadData['transcript'];
            }

            if (!empty($parts)) {
                $leadData['notes'] = implode("\n", $parts);
            }
        }

        return $leadData;
    }
}

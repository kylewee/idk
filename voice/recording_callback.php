<?php
declare(strict_types=1);

function extract_customer_data_ai($transcript) {
  if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) {
    // Fallback to pattern matching if no AI configured
    return extract_customer_data_patterns($transcript);
  }
  
  $prompt = "You are analyzing a phone call transcript for a mobile mechanic service. Extract customer information and return ONLY a JSON object with these exact keys (use null for missing data):

{
  \"first_name\": \"customer's first name\",
  \"last_name\": \"customer's last name\", 
  \"phone\": \"phone number in format like 9045551234 (digits only)\",
  \"address\": \"location/address mentioned\",
  \"year\": \"vehicle year (4 digits)\",
  \"make\": \"vehicle make/brand\",
  \"model\": \"vehicle model\",
  \"engine\": \"engine size/type if mentioned\",
  \"notes\": \"problem description or service needed\"
}

Rules:
- Extract actual customer info, not business/agent details
- For phone: digits only, no formatting
- For year: must be 4-digit year between 1990-2030
- For make/model: standardize common brands (Honda, Toyota, etc.)
- For notes: summarize the actual problem/service needed
- Return null for any field that's not clearly stated

Transcript: " . $transcript;

  $payload = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
      ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 300,
    'temperature' => 0.1
  ];

  $ch = curl_init('https://api.openai.com/v1/chat/completions');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . OPENAI_API_KEY,
      'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
  ]);
  
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $error = curl_error($ch);
  curl_close($ch);
  
  if ($error || $httpCode !== 200) {
    error_log("VOICE_AI: OpenAI API error - HTTP $httpCode: $error");
    return extract_customer_data_patterns($transcript);
  }
  
  $data = json_decode($response, true);
  if (!$data || !isset($data['choices'][0]['message']['content'])) {
    error_log("VOICE_AI: Invalid OpenAI response format");
    return extract_customer_data_patterns($transcript);
  }
  
  $aiResponse = trim($data['choices'][0]['message']['content']);
  $extracted = json_decode($aiResponse, true);
  
  if (!is_array($extracted)) {
    error_log("VOICE_AI: Failed to parse AI response as JSON: " . $aiResponse);
    return extract_customer_data_patterns($transcript);
  }
  
  // Clean and validate the AI response
  $result = [];
  foreach (['first_name', 'last_name', 'phone', 'address', 'year', 'make', 'model', 'engine', 'notes'] as $field) {
    $value = $extracted[$field] ?? null;
    if ($value && trim($value) && strtolower(trim($value)) !== 'null') {
      $result[$field] = trim($value);
    }
  }
  
  // Validate phone number format
  if (!empty($result['phone'])) {
    $result['phone'] = preg_replace('/[^\d]/', '', $result['phone']);
    if (strlen($result['phone']) < 10) {
      unset($result['phone']);
    }
  }
  
  // Validate year
  if (!empty($result['year'])) {
    $year = (int)$result['year'];
    if ($year < 1990 || $year > 2030) {
      unset($result['year']);
    }
  }
  
  error_log("VOICE_AI: Extracted data: " . json_encode($result));
  return $result;
}

function extract_customer_data(string $transcript): array {
  // Try AI extraction first, fallback to patterns if needed
  return extract_customer_data_ai($transcript);
}

function extract_customer_data_patterns(string $transcript): array {
  $out = [];
  $text = trim((string)$transcript);

  // Helper to title-case names
  $cap = function(string $s): string {
    $s = strtolower($s);
    return preg_replace_callback('/\b([a-z])([a-z]*)/', function($m){ return strtoupper($m[1]) . $m[2]; }, $s) ?? $s;
  };

  // 1) Labeled extraction with more flexible separators
  $defs = [
    'first name'   => 'first_name',
    'last name'    => 'last_name',
    'first'        => 'first_name',
    'last'         => 'last_name',
    'fname'        => 'first_name',
    'lname'        => 'last_name',
    'name'         => 'name',
    'phone'        => 'phone',
    'phone number' => 'phone',
    'address'      => 'address',
    'year'         => 'year',
    'make'         => 'make',
    'model'        => 'model',
    'engine size'  => 'engine_size',
    'notes'        => 'notes',
    'special notes'=> 'notes',
  ];

  // Build list of label phrases for lookahead termination
  $labelPhrases = array_keys($defs);

  foreach ($defs as $needle => $field) {
    // Terminate capture when encountering the next known label phrase OR punctuation/OK
    $otherLabels = array_filter($labelPhrases, function($k) use ($needle) { return $k !== $needle; });
    $otherLabelsQuoted = array_map(function($s){ return preg_quote($s, '/'); }, $otherLabels);
    $lookahead = '(?=(?:\b(?:' . implode('|', $otherLabelsQuoted) . ')\b)|\s*\bok\b|[\.;,\n\r]|$)';

    $re = '/\b' . preg_quote($needle, '/') . '\b\s*(?:is\s*)?[:\-]?\s*(.*?)\s*' . $lookahead . '/i';
    if (preg_match($re, $text, $m)) {
      $val = trim($m[1]);
      $val = preg_replace('/^(is|it|the|a|an)\s+/i', '', $val);
      if ($field === 'year' && preg_match('/\b(19|20)\d{2}\b/', $val, $ym)) {
        $val = $ym[0];
      }
      if ($field === 'first_name' || $field === 'last_name' || $field === 'name') {
        $val = $cap($val);
      }
      // Normalize phone to digits+plus when captured via label
      if ($field === 'phone' && $val !== '') {
        $val = preg_replace('/[^\d\+]/', '', $val);
      }
      $out[$field] = $val;
    }
  }

  // 2) Natural language patterns e.g., "my name is John Smith", "this is John", "I'm John", "I am John"
  if (empty($out['name']) && (preg_match('/\b(?:my\s+name\s+is|this\s+is|i\s*am|i\'m|it\'s)\s+([a-z][a-z\-\']+)(?:\s+([a-z][a-z\-\']+))?/i', $text, $nm))) {
    $fn = $cap($nm[1] ?? '');
    $ln = $cap($nm[2] ?? '');
    // If second token looks like a location preposition, treat as first name only
    if ($ln && preg_match('/^(from|in|at|of)$/i', $ln)) {
      $ln = '';
    }
    if ($fn && $ln) {
      $out['first_name'] = $fn; $out['last_name'] = $ln; $out['name'] = "$fn $ln";
    } elseif ($fn) {
      $out['first_name'] = $fn; $out['name'] = $fn;
    }
  }

  // 3) If only combined name present, split into first/last by first two tokens
  if (!empty($out['name']) && (empty($out['first_name']) && empty($out['last_name']))) {
    $parts = preg_split('/\s+/', trim($out['name']));
    if ($parts && count($parts) >= 2) {
      $out['first_name'] = $cap($parts[0]);
      $out['last_name']  = $cap(implode(' ', array_slice($parts, 1)));
    } elseif ($parts && count($parts) === 1) {
      $out['first_name'] = $cap($parts[0]);
    }
  }

  // 4) Phone number extraction (best-effort)
  if (!preg_match('/\d{7,}/', (string)($out['phone'] ?? ''))) {
    if (preg_match('/\b(\+?\d[\d\s\-().]{6,}\d)\b/', $text, $pm)) {
      $out['phone'] = preg_replace('/[^\d\+]/', '', $pm[1]);
    }
  }

  // 5) Free-form vehicle pattern: e.g., "2012 Honda Civic", capture year/make/model if not already set
  if (empty($out['year']) || empty($out['make']) || empty($out['model'])) {
    if (preg_match('/\b((?:19|20)\d{2})\b\s+([A-Za-z][A-Za-z0-9\-]+)\s+([A-Za-z0-9][A-Za-z0-9\-]+)\b/', $text, $vm)) {
      if (empty($out['year']))  $out['year']  = $vm[1];
      if (empty($out['make']))  $out['make']  = $cap($vm[2]);
      if (empty($out['model'])) $out['model'] = $cap($vm[3]);
    }
  }

  // 6) Engine size quick patterns: "2.4 liter", "2.4L", "V6 3.5"
  if (empty($out['engine_size'])) {
    if (preg_match('/\b(\d\.\d)\s*(l|liter|litre)\b/i', $text, $em)) {
      $out['engine_size'] = $em[1] . 'L';
    } elseif (preg_match('/\b(v(?:6|8)|v6|v8)\s*(\d\.\d)?\b/i', $text, $em)) {
      $out['engine_size'] = strtoupper($em[1]) . (isset($em[2]) && $em[2] ? ' ' . $em[2] . 'L' : '');
    }
  }

  return $out;
}

function create_crm_lead(array $leadData): array {
  if (!defined('CRM_API_URL') || !defined('CRM_LEADS_ENTITY_ID')) {
    return ['error'=>'CRM config missing'];
  }

  $post = [
    'action'    => 'add_item',
    'entity_id' => (int)CRM_LEADS_ENTITY_ID,
  ];

  // Prefer token auth; fallback to API key
  if (defined('CRM_USERNAME') && CRM_USERNAME && defined('CRM_PASSWORD') && CRM_PASSWORD) {
    $loginCh = curl_init(CRM_API_URL);
    curl_setopt_array($loginCh, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => [
        'action'   => 'login',
        'username' => CRM_USERNAME,
        'password' => CRM_PASSWORD,
        'key'      => (defined('CRM_API_KEY') ? CRM_API_KEY : ''),
      ],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 8,
      CURLOPT_TIMEOUT => 15,
    ]);
    $loginResp = curl_exec($loginCh);
    $loginHttp = (int)curl_getinfo($loginCh, CURLINFO_HTTP_CODE);
    curl_close($loginCh);
    $loginJson = json_decode((string)$loginResp, true);
    if (is_array($loginJson) && !empty($loginJson['token'])) {
      $post['token'] = (string)$loginJson['token'];
    } elseif (defined('CRM_API_KEY') && CRM_API_KEY) {
      $post['key'] = CRM_API_KEY;
    } else {
      // still try DB fallback below
    }
  } elseif (defined('CRM_API_KEY') && CRM_API_KEY) {
    $post['key'] = CRM_API_KEY;
  }

  // Synthesize name if needed
  if (empty($leadData['name'])) {
    $fn = trim((string)($leadData['first_name'] ?? ''));
    $ln = trim((string)($leadData['last_name'] ?? ''));
    if ($fn || $ln) {
      $leadData['name'] = trim($fn . ' ' . $ln);
    }
  }
  // Synthesize notes if mapped but missing
  if (defined('CRM_FIELD_MAP') && is_array(CRM_FIELD_MAP) && !isset($leadData['notes']) && isset(CRM_FIELD_MAP['notes'])) {
    $leadData['notes'] = "Recording: " . ($leadData['recording_url'] ?? '') . "\nTranscript: " . ($leadData['transcript'] ?? '');
  }

  // Field mapping (allow auto-discovery for missing IDs)
  $fieldMap = resolve_field_map();
  if (is_array($fieldMap)) {
    foreach ($fieldMap as $key => $fid) {
      $fid = (int)$fid;
      if ($fid <= 0) continue;
      if (!array_key_exists($key, $leadData)) continue;
      $val = (string)$leadData[$key];
      if ($val === '') continue;
      $post['fields[field_' . $fid . ']'] = $val;
    }
  }

  // Try REST API
  $ch = curl_init(CRM_API_URL);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 15,
  ]);
  $resp = curl_exec($ch);
  $errno = curl_errno($ch);
  $err  = curl_error($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $result = ['http'=>$http, 'curl_errno'=>$errno, 'curl_error'=>$err, 'body'=>$resp];

  $shouldFallback = ($errno !== 0) || ($http >= 500) || ($resp === false);
  if ($shouldFallback) {
    $dbRes = create_crm_lead_db_insert($leadData);
    $result['fallback'] = $dbRes;
  }

  return $result;
}

/**
 * Direct DB insert fallback for creating a lead in Rukovoditel when REST API is unavailable.
 * Inserts into table app_entity_{CRM_LEADS_ENTITY_ID} using mapped field_* columns.
 */
function create_crm_lead_db_insert(array $leadData): array {
  $out = ['ok' => false];

  if (!defined('CRM_LEADS_ENTITY_ID') || (int)CRM_LEADS_ENTITY_ID <= 0) {
    return ['ok'=>false,'error'=>'leads_entity_id_missing'];
  }

  try {
    $cfg = crm_db_config();
    if (!$cfg) {
      return ['ok'=>false,'error'=>'db_config_missing'];
    }

    $mysqli = @new mysqli($cfg['db_host'], $cfg['db_username'], $cfg['db_password'], $cfg['db_name']);
    if ($mysqli->connect_errno) {
      return ['ok'=>false,'error'=>'db_connect','detail'=>$mysqli->connect_error];
    }

  $entityId = (int)CRM_LEADS_ENTITY_ID;
  $table = 'app_entity_' . $entityId;

    $columns = [];
    $values  = [];
    $types   = '';
    $seenCols = [];

    // Base columns for visibility (using actual table columns)
    $createdBy = defined('CRM_CREATED_BY_USER_ID') ? (int)CRM_CREATED_BY_USER_ID : 1;
    $dateAdded = time();
    foreach ([
      'created_by'     => $createdBy,
      'date_added'     => $dateAdded,
      'parent_item_id' => 0,
      'sort_order'     => 0,
    ] as $col => $val) {
      $columns[] = "`$col`";
      $values[]  = $val;
      $types    .= 'i';
      $seenCols[$col] = true;
    }

    // Synthesize name
    if (empty($leadData['name'])) {
      $fn = trim((string)($leadData['first_name'] ?? ''));
      $ln = trim((string)($leadData['last_name'] ?? ''));
      if ($fn || $ln) $leadData['name'] = trim($fn . ' ' . $ln);
    }

  // Duplicate prevention: if a phone field is mapped and provided, avoid inserting duplicates within last 60 minutes
  // Allow bypass via testing flag _bypass_dedupe
  $resolvedMap = resolve_field_map();
  $phoneFieldId = (isset($resolvedMap['phone']) ? (int)$resolvedMap['phone'] : 0);
    if ($phoneFieldId > 0 && empty($leadData['_bypass_dedupe'])) {
      $phoneVal = '';
      if (!empty($leadData['phone'])) {
        $phoneVal = (string)$leadData['phone'];
      }
      // Normalize phone to digits+plus for comparison
      $phoneValNorm = preg_replace('/[^\d\+]/', '', $phoneVal ?? '');
      if ($phoneValNorm !== '') {
        $phoneCol = 'field_' . $phoneFieldId;
        $oneHourAgo = time() - 3600;
        $q = 'SELECT `id` FROM `' . $mysqli->real_escape_string($table) . '` WHERE `' . $phoneCol . '` = ? AND `date_added` > ? ORDER BY `id` DESC LIMIT 1';
        $stmt = $mysqli->prepare($q);
        if ($stmt) {
          $stmt->bind_param('si', $phoneValNorm, $oneHourAgo);
          if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
              $existingId = (int)$row['id'];
              $stmt->close();

              // Attempt to update empty fields on the existing lead with any new non-empty values
              $fieldMap = resolve_field_map();
              $updateCols = [];
              $updateVals = [];
              $updateTypes = '';

              // Fetch existing values for mapped columns to know which are empty
              $colsToCheck = [];
              foreach ($fieldMap as $key => $fid) {
                $fid = (int)$fid; if ($fid <= 0) continue;
                $col = 'field_' . $fid; $colsToCheck[$key] = $col;
              }
              if (!empty($colsToCheck)) {
                $selCols = array_values($colsToCheck);
                $selSql = 'SELECT `' . implode('`,`', $selCols) . '` FROM `' . $mysqli->real_escape_string($table) . '` WHERE `id` = ? LIMIT 1';
                $sel = $mysqli->prepare($selSql);
                if ($sel) {
                  $sel->bind_param('i', $existingId);
                  if ($sel->execute()) {
                    $er = $sel->get_result();
                    $current = $er ? $er->fetch_assoc() : null;
                    if (is_array($current)) {
                      foreach ($fieldMap as $key => $fid) {
                        $fid = (int)$fid; if ($fid <= 0) continue;
                        if (!array_key_exists($key, $leadData)) continue; // nothing new to set
                        $val = (string)$leadData[$key];
                        if ($key === 'phone') { $val = preg_replace('/[^\d\+]/', '', $val); }
                        if ($val === '') continue; // empty new value
                        $col = 'field_' . $fid;
                        $curr = (string)($current[$col] ?? '');
                        if ($curr === '' || $curr === '0') {
                          $updateCols[] = '`' . $col . '` = ?';
                          $updateVals[] = $val;
                          $updateTypes .= 's';
                        }
                      }
                    }
                  }
                  $sel->close();
                }
              }

              if (!empty($updateCols)) {
                // Optionally update date_updated if column exists
                $has_date_updated = false;
                if ($res2 = $mysqli->query('SHOW COLUMNS FROM `' . $mysqli->real_escape_string($table) . "` LIKE 'date_updated'")) {
                  $has_date_updated = (bool)$res2->num_rows; $res2->free();
                }
                if ($has_date_updated) {
                  $updateCols[] = '`date_updated` = ?';
                  $updateVals[] = time();
                  $updateTypes .= 'i';
                }

                $updSql = 'UPDATE `' . $mysqli->real_escape_string($table) . '` SET ' . implode(',', $updateCols) . ' WHERE `id` = ?';
                $upd = $mysqli->prepare($updSql);
                if ($upd) {
                  $updateTypes .= 'i';
                  $updateVals[] = $existingId;
                  $upd->bind_param($updateTypes, ...$updateVals);
                  $upd->execute();
                  $upd->close();
                }
              }

              $mysqli->close();
              return ['ok'=>true,'id'=>$existingId,'duplicate'=>true,'updated'=>!empty($updateCols)];
            }
          }
          $stmt->close();
        }
      }
    }

    // Map fields -> field_<id>
    $fieldMap = resolve_field_map();
    if (is_array($fieldMap)) {
      foreach ($fieldMap as $key => $fid) {
        $fid = (int)$fid;
        if ($fid <= 0 || !array_key_exists($key, $leadData)) continue;
        $val = (string)$leadData[$key];
        if ($key === 'phone') {
          // Normalize phone format for storage
          $val = preg_replace('/[^\d\+]/', '', $val);
        }
        if ($val === '') continue;
        $col = 'field_' . $fid;
        if (isset($seenCols[$col])) continue;
        $seenCols[$col] = true;
        $columns[] = '`' . $col . '`';
        $values[]  = $val;
        $types    .= 's';
      }
    }

    // Notes synthesis if mapped
    if (defined('CRM_FIELD_MAP') && is_array(CRM_FIELD_MAP) && isset(CRM_FIELD_MAP['notes'])) {
      $notesFieldId = (int)CRM_FIELD_MAP['notes'];
      if ($notesFieldId > 0) {
        $notesCol = 'field_' . $notesFieldId;
        if (!isset($seenCols[$notesCol]) && (!empty($leadData['transcript']) || !empty($leadData['recording_url']))) {
          $columns[] = '`' . $notesCol . '`';
          $values[]  = "Recording: " . ($leadData['recording_url'] ?? '') . "\nTranscript: " . ($leadData['transcript'] ?? '');
          $types    .= 's';
          $seenCols[$notesCol] = true;
        }
      }
    }

    // Ensure non-nullable field_* columns without defaults get safe values to satisfy strict SQL modes
    // This covers newly added fields (e.g., stage/source) that may be NOT NULL with no default.
    $colInfo = $mysqli->query('SHOW COLUMNS FROM `' . $mysqli->real_escape_string($table) . '`');
    if ($colInfo) {
      while ($col = $colInfo->fetch_assoc()) {
        $cname = $col['Field'] ?? '';
        if (strpos($cname, 'field_') !== 0) continue; // only user fields
        if (isset($seenCols[$cname])) continue; // already set
        $nullable = strtolower((string)($col['Null'] ?? ''));
        $default  = $col['Default'] ?? null;
        if ($nullable === 'no' && $default === null) {
          $ctype = strtolower((string)($col['Type'] ?? ''));
          // Decide a safe fallback value: numeric-like -> 0, else ''
          $isNumeric = (strpos($ctype, 'int') !== false) || (strpos($ctype, 'decimal') !== false) || (strpos($ctype, 'float') !== false) || (strpos($ctype, 'double') !== false);
          $fallbackVal = $isNumeric ? 0 : '';
          $columns[] = '`' . $cname . '`';
          $values[]  = $fallbackVal;
          $types    .= $isNumeric ? 'i' : 's';
          $seenCols[$cname] = true;
        }
      }
      $colInfo->close();
    }

    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $sql = 'INSERT INTO `' . $mysqli->real_escape_string($table) . '` (' . implode(',', $columns) . ') VALUES (' . $placeholders . ')';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
      $err = $mysqli->error;
      $mysqli->close();
      return ['ok'=>false,'error'=>'prepare','detail'=>$err,'sql'=>$sql];
    }

    $stmt->bind_param($types, ...$values);
    if (!$stmt->execute()) {
      $err = $stmt->error;
      $stmt->close();
      $mysqli->close();
      return ['ok'=>false,'error'=>'execute','detail'=>$err,'sql'=>$sql];
    }

    $id = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();
    return ['ok'=>true,'id'=>$id];
  } catch (Throwable $e) {
    return ['ok'=>false,'error'=>'exception','detail'=>$e->getMessage()];
  }
}

/**
 * Load CRM DB config from Rukovoditel (supports both array and constants styles)
 */
function crm_db_config(): ?array {
  $file = __DIR__ . '/../crm/config/database.php';
  if (!is_file($file)) return null;

  // Include the config file which defines DB_* constants
  require_once $file;

  // Debug: Check what constants are defined
  $allConstants = get_defined_constants(true);
  $userConstants = $allConstants['user'] ?? [];
  
  // Check if constants are defined (most common Rukovoditel style)
  if (defined('DB_SERVER') && defined('DB_SERVER_USERNAME') && defined('DB_DATABASE')) {
    return [
      'db_host' => (string)DB_SERVER,
      'db_username' => (string)DB_SERVER_USERNAME,
      'db_password' => (string)(defined('DB_SERVER_PASSWORD') ? DB_SERVER_PASSWORD : ''),
      'db_name' => (string)DB_DATABASE,
    ];
  }

  // Alternative constant names
  if (defined('DB_HOST') && defined('DB_USERNAME') && defined('DB_NAME')) {
    return [
      'db_host' => (string)DB_HOST,
      'db_username' => (string)DB_USERNAME, 
      'db_password' => (string)(defined('DB_PASSWORD') ? DB_PASSWORD : ''),
      'db_name' => (string)DB_NAME,
    ];
  }

  // Debug: Log which DB-related constants we found
  $dbConstants = array_filter($userConstants, function($key) {
    return strpos($key, 'DB_') === 0;
  }, ARRAY_FILTER_USE_KEY);
  error_log('DB_CONFIG_DEBUG: found constants=' . json_encode($dbConstants));

  return null;
}

/**
 * Merge configured CRM_FIELD_MAP with auto-discovered field IDs from CRM DB for entity CRM_LEADS_ENTITY_ID.
 * Only fills keys where configured value <= 0. Returns associative array key => field_id.
 */
function resolve_field_map(): array {
  $map = [];
  if (defined('CRM_FIELD_MAP') && is_array(CRM_FIELD_MAP)) {
    $map = CRM_FIELD_MAP;
  }
  // Attempt auto-discovery
  $auto = crm_autodiscover_fields();
  if (!empty($auto)) {
    foreach ($auto as $k => $fid) {
      if (!isset($map[$k]) || (int)$map[$k] <= 0) {
        $map[$k] = (int)$fid;
      }
    }
  }
  return $map;
}

/**
 * Query Rukovoditel app_fields to infer likely field IDs by matching labels.
 * Non-fatal: returns empty on failure.
 */
function crm_autodiscover_fields(): array {
  $out = [];
  if (!defined('CRM_LEADS_ENTITY_ID') || (int)CRM_LEADS_ENTITY_ID <= 0) return $out;
  try {
    $cfg = crm_db_config();
    if (!$cfg) return $out;
    $mysqli = @new mysqli($cfg['db_host'], $cfg['db_username'], $cfg['db_password'], $cfg['db_name']);
    if ($mysqli->connect_errno) return $out;

    $eid = (int)CRM_LEADS_ENTITY_ID;
    $sql = 'SELECT id, name FROM app_fields WHERE entities_id = ?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { $mysqli->close(); return $out; }
    $stmt->bind_param('i', $eid);
    if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return $out; }
    $res = $stmt->get_result();
    $labels = [];
    while ($row = $res->fetch_assoc()) {
      $labels[(int)$row['id']] = strtolower(trim((string)$row['name']));
    }
    $stmt->close();
    $mysqli->close();

    if (empty($labels)) return $out;

    $syn = [
      'first_name'  => ['first name','first','fname','given name'],
      'last_name'   => ['last name','last','lname','surname','family name'],
      'phone'       => ['phone','phone number','telephone','mobile','cell'],
      'address'     => ['address','street','address line','location'],
      'year'        => ['year','vehicle year'],
      'make'        => ['make','vehicle make','brand','manufacturer'],
      'model'       => ['model','vehicle model','trim'],
      'engine_size' => ['engine size','engine','displacement'],
      'notes'       => ['notes','note','comments','comment','details','description'],
      'name'        => ['name','full name','contact name','lead name'],
    ];

    // For each field label, try to match synonym lists. Prefer exact equals, else substring contains.
    foreach ($labels as $fid => $label) {
      foreach ($syn as $key => $words) {
        if (isset($out[$key])) continue; // already matched
        foreach ($words as $w) {
          if ($label === $w || strpos($label, $w) !== false) {
            $out[$key] = (int)$fid;
            break 2;
          }
        }
      }
    }
  } catch (Throwable $e) {
    // non-fatal
  }
  return $out;
}
// Load configuration as early as possible for all routes (download/recordings/dial)
require_once __DIR__ . '/../config/config.php';

// Inline router for auxiliary features so we don't depend on separate files
$__action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper: compact logger for these routes
function voice_log_event(string $event, array $data = []): void {
  $row = array_merge(['ts' => date('c'), 'event' => $event, 'ip' => ($_SERVER['REMOTE_ADDR'] ?? '')], $data);
  $line = json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
  @file_put_contents(__DIR__ . '/voice.log', $line, FILE_APPEND);
}

// --- Access control helpers for recordings/download ---
function recordings_basic_configured(): bool {
  return defined('VOICE_RECORDINGS_BASIC_USER') && VOICE_RECORDINGS_BASIC_USER !== ''
      && defined('VOICE_RECORDINGS_BASIC_PASS') && VOICE_RECORDINGS_BASIC_PASS !== '';
}
function recordings_password_configured(): bool {
  return defined('VOICE_RECORDINGS_PASSWORD') && VOICE_RECORDINGS_PASSWORD !== '';
}
function recordings_token_configured(): bool {
  return defined('VOICE_RECORDINGS_TOKEN') && VOICE_RECORDINGS_TOKEN !== '';
}
function recordings_basic_enforce(): void {
  if (!recordings_basic_configured()) return; // nothing to do
  $u = $_SERVER['PHP_AUTH_USER'] ?? '';
  $p = $_SERVER['PHP_AUTH_PW'] ?? '';
  if ($u !== (string)VOICE_RECORDINGS_BASIC_USER || $p !== (string)VOICE_RECORDINGS_BASIC_PASS) {
    header('WWW-Authenticate: Basic realm="Recordings"');
    http_response_code(401);
    echo 'Authentication required';
    exit;
  }
}

if ($__action === 'download') {
  // Securely proxy a Twilio recording by Recording SID
  // Access control (Basic > Password > Token)
  if (recordings_basic_configured()) {
    recordings_basic_enforce();
  } elseif (recordings_password_configured()) {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $ok = !empty($_SESSION['recordings_auth_ok']);
    if (!$ok) {
      // If a password is provided via POST, check it first
      $passTry = isset($_POST['password']) ? (string)$_POST['password'] : '';
      if ($passTry !== '') {
        if ($passTry === (string)VOICE_RECORDINGS_PASSWORD) {
          $_SESSION['recordings_auth_ok'] = true;
          $ok = true;
        }
      }
      if (!$ok) {
        // fallback to token check in query
        $tokenParam = (string)($_GET['token'] ?? '');
        $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
        if ($tokenCfg !== '' && hash_equals($tokenCfg, $tokenParam)) {
          $ok = true; // allow via token
        }
      }
    }
    if (!$ok) {
      http_response_code(403);
      echo 'Forbidden';
      exit;
    }
  } else {
    // Only token mode
    $tokenParam = (string)($_GET['token'] ?? '');
    $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
    if ($tokenCfg !== '' && !hash_equals($tokenCfg, $tokenParam)) {
      http_response_code(403);
      echo 'Forbidden';
      voice_log_event('download_forbidden');
      exit;
    }
  }
  $sid = isset($_GET['sid']) ? preg_replace('/[^A-Za-z0-9]/', '', (string)$_GET['sid']) : '';
  // (token already validated above when relevant)
  if ($sid === '') {
    http_response_code(400);
    echo 'Missing sid';
    exit;
  }
  $acct = defined('TWILIO_ACCOUNT_SID') ? (string)TWILIO_ACCOUNT_SID : '';
  $auth = defined('TWILIO_AUTH_TOKEN') ? (string)TWILIO_AUTH_TOKEN : '';
  if ($acct === '' || $auth === '') {
    http_response_code(500);
    echo 'Twilio credentials not configured';
    exit;
  }
  $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($acct) . '/Recordings/' . rawurlencode($sid) . '.mp3';
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_USERPWD => $acct . ':' . $auth,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 8,
  ]);
  $bin = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errn = curl_errno($ch);
  $erre = curl_error($ch);
  curl_close($ch);
  if ($errn !== 0 || $http < 200 || $http >= 300 || $bin === false) {
    http_response_code(502);
    echo 'Failed to fetch recording';
    voice_log_event('download_error', ['sid' => $sid, 'http' => $http, 'errno' => $errn, 'error' => $erre]);
    exit;
  }
  $dl = isset($_GET['download']) && ($_GET['download'] === '1' || $_GET['download'] === 'true');
  header('Content-Type: audio/mpeg');
  if ($dl) header('Content-Disposition: attachment; filename="' . $sid . '.mp3"');
  header('Cache-Control: private, max-age=0');
  voice_log_event('download_ok', ['sid' => $sid]);
  echo $bin;
  exit;
}

if ($__action === 'recordings') {
  // Access control: Basic > Password form > Token
  if (recordings_basic_configured()) {
    recordings_basic_enforce();
  } elseif (recordings_password_configured()) {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $ok = !empty($_SESSION['recordings_auth_ok']);
    if (!$ok) {
      $passTry = isset($_POST['password']) ? (string)$_POST['password'] : '';
      if ($passTry !== '' && $passTry === (string)VOICE_RECORDINGS_PASSWORD) {
        $_SESSION['recordings_auth_ok'] = true;
        $ok = true;
      }
    }
    if (!$ok) {
      header('Content-Type: text/html; charset=UTF-8');
      echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
      echo '<title>Recordings Login</title>';
      echo '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;margin:40px;background:#0f172a;color:#e2e8f0} .wrap{max-width:420px;margin:0 auto;background:#111827;padding:24px;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.4)} h1{font-size:18px;margin:0 0 16px} form{display:flex;gap:8px} input[type=password]{flex:1;padding:10px;border-radius:8px;border:1px solid #374151;background:#0b1220;color:#e2e8f0} button{padding:10px 14px;border-radius:8px;border:0;background:#2563eb;color:#fff;cursor:pointer} .hint{margin-top:10px;color:#94a3b8;font-size:12px}</style>';
      echo '</head><body><div class="wrap">';
      echo '<h1>Enter password</h1>';
      echo '<form method="post"><input type="password" name="password" placeholder="Password" autofocus required><button type="submit">Continue</button></form>';
      echo '<div class="hint">Tip: Set VOICE_RECORDINGS_BASIC_USER/PASS for HTTP Basic, or VOICE_RECORDINGS_TOKEN to use a tokenized link.</div>';
      echo '</div></body></html>';
      exit;
    }
  } else {
    // Token mode only
    $tokenParam = (string)($_GET['token'] ?? '');
    $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
    if ($tokenCfg !== '' && !hash_equals($tokenCfg, $tokenParam)) {
      http_response_code(403);
      echo 'Forbidden';
      exit;
    }
  }
  $host = $_SERVER['HTTP_HOST'] ?? 'mechanicstaugustine.com';
  $logFile = __DIR__ . '/voice.log';
  $rows = [];
  if (is_file($logFile)) {
    // Read last ~1500 lines to avoid huge memory; degrade gracefully if larger
    $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $take = 1500;
    if (count($lines) > $take) $lines = array_slice($lines, -$take);
    foreach (array_reverse($lines) as $line) {
      $j = json_decode($line, true);
      if (!is_array($j)) continue;
      $ts = $j['ts'] ?? '';
      // Attempt to find recording info
      $post = $j['post'] ?? [];
      $summary = $j['summary'] ?? [];
      $sid = '';
      $dur = 0;
      $from = '';
      $to = '';
      $trans = '';
      if (isset($post['RecordingSid'])) $sid = (string)$post['RecordingSid'];
      if ($sid === '' && isset($summary['url']) && preg_match('/Recordings\/([^\/]+)$/', (string)$summary['url'], $m)) $sid = $m[1];
      if ($sid === '' && isset($post['RecordingUrl']) && preg_match('/Recordings\/([^\/]+)$/', (string)$post['RecordingUrl'], $m)) $sid = $m[1];
      if ($sid === '') continue;
      $dur = (int)($post['RecordingDuration'] ?? ($summary['duration'] ?? 0));
      $from = (string)($post['From'] ?? $post['Caller'] ?? ($summary['from'] ?? ''));
      $to   = (string)($post['To']   ?? $post['Called'] ?? ($summary['to'] ?? ''));
      // Transcript: prefer summary.transcript, fallback to post.TranscriptionText
      if (!empty($summary['transcript'])) $trans = (string)$summary['transcript'];
      elseif (!empty($post['TranscriptionText'])) $trans = (string)$post['TranscriptionText'];

      // If row exists, update transcript if we don't have one yet and found a newer non-empty
      if (isset($rows[$sid])) {
        if (empty($rows[$sid]['transcript']) && $trans !== '') {
          $rows[$sid]['transcript'] = $trans;
        }
        // Keep earliest ts and from/to if those were empty previously; but transcript update is main target
        if ($rows[$sid]['ts'] === '' && $ts !== '') $rows[$sid]['ts'] = $ts;
        if ($rows[$sid]['from'] === '' && $from !== '') $rows[$sid]['from'] = $from;
        if ($rows[$sid]['to'] === '' && $to !== '') $rows[$sid]['to'] = $to;
        continue;
      }
      $rows[$sid] = [
        'ts' => $ts,
        'sid' => $sid,
        'duration' => $dur,
        'from' => $from,
        'to' => $to,
        'transcript' => $trans,
      ];
      if (count($rows) >= 200) break; // cap list
    }
  }

  header('Content-Type: text/html; charset=UTF-8');
  echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
  echo '<title>Call Recordings</title>';
  echo '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;margin:20px;background:#f8fafc;color:#0f172a}h1{font-size:20px}table{border-collapse:collapse;width:100%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05)}th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:left}th{background:#f1f5f9}tr:hover{background:#f8fafc}code{background:#e2e8f0;padding:2px 4px;border-radius:4px}a.button{display:inline-block;padding:6px 10px;background:#2563eb;color:#fff;text-decoration:none;border-radius:4px}audio{width:240px}</style>';
  echo '</head><body>';
  echo '<h1>Recent Call Recordings</h1>';
  $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
  $nativeTranscriptEnabled = (defined('TWILIO_TRANSCRIBE_ENABLED') && TWILIO_TRANSCRIBE_ENABLED);
  $whisperEnabled = (defined('OPENAI_API_KEY') && OPENAI_API_KEY);
  if ($nativeTranscriptEnabled) {
    echo '<p style="background:#ecfeff;border:1px solid #06b6d4;color:#0e7490;padding:10px;border-radius:6px">Transcription via Twilio CI is enabled. New recordings without transcripts can be enqueued manually with “Transcribe now”.</p>';
  } elseif ($whisperEnabled) {
    echo '<p style="background:#e0f2fe;border:1px solid #38bdf8;color:#075985;padding:10px;border-radius:6px">Automatic CI is not configured, but on-demand transcription via OpenAI Whisper is available. Use “Transcribe now”.</p>';
  } else {
    echo '<p style="background:#fff3c4;border:1px solid #facc15;color:#854d0e;padding:10px;border-radius:6px">Transcription is disabled (no CI_SERVICE_SID and no OPENAI_API_KEY). Set one in .env.local.php. You can still play/download recordings.</p>';
  }
  if ($tokenCfg === '' && !recordings_basic_configured() && !recordings_password_configured()) {
    echo '<p><strong>Warning:</strong> No access token configured. Add VOICE_RECORDINGS_TOKEN in .env.local.php to restrict access.</p>';
  }
  echo '<table><thead><tr><th>When</th><th>From</th><th>To</th><th>Duration</th><th>Play</th><th>Link</th></tr></thead><tbody>';
  foreach ($rows as $r) {
    $sid = htmlspecialchars($r['sid'], ENT_QUOTES);
    $link = 'https://' . $host . '/voice/recording_callback.php?action=download&sid=' . rawurlencode($sid);
  if ($tokenCfg !== '' && !recordings_basic_configured() && !recordings_password_configured()) $link .= '&token=' . rawurlencode($tokenCfg);
    $ts = htmlspecialchars((string)$r['ts'], ENT_QUOTES);
    $from = htmlspecialchars((string)$r['from'], ENT_QUOTES);
    $to = htmlspecialchars((string)$r['to'], ENT_QUOTES);
    $dur = (int)$r['duration'];
    $transcriptRaw = (string)($r['transcript'] ?? '');
    $transcriptEsc = htmlspecialchars($transcriptRaw, ENT_QUOTES);
    // for long transcripts, show first 200 chars + details
    $preview = mb_substr($transcriptEsc, 0, 200);
    $hasMore = (mb_strlen($transcriptEsc) > 200);
    echo '<tr>';
    echo '<td>' . $ts . '</td>';
    echo '<td>' . $from . '</td>';
    echo '<td>' . $to . '</td>';
    echo '<td>' . ($dur ? $dur . 's' : '-') . '</td>';
    echo '<td><audio controls src="' . htmlspecialchars($link, ENT_QUOTES) . '"></audio>';
    if ($transcriptRaw !== '') {
      echo '<div class="transcript" style="margin-top:8px;font-size:12px;color:#475569;line-height:1.4">';
      echo '<strong>Transcript:</strong> ';
      if ($hasMore) {
        echo $preview . '… <details style="display:inline"><summary style="display:inline;cursor:pointer;color:#2563eb">more</summary><div style="margin-top:6px;color:#334155;white-space:pre-wrap">' . nl2br($transcriptEsc) . '</div></details>';
      } else {
        echo '<span style="white-space:pre-wrap">' . nl2br($transcriptEsc) . '</span>';
      }
      echo '</div>';
    }
    echo '</td>';
    echo '<td>';
    echo '<a class="button" href="' . htmlspecialchars($link, ENT_QUOTES) . '&download=1">Download</a>';
    if (($nativeTranscriptEnabled || $whisperEnabled) && $transcriptRaw === '') {
      $txUrl = 'https://' . $host . '/voice/recording_callback.php?action=transcribe&sid=' . rawurlencode($sid);
      if ($tokenCfg !== '' && !recordings_basic_configured() && !recordings_password_configured()) $txUrl .= '&token=' . rawurlencode($tokenCfg);
      echo ' <a class="button" style="background:#059669" href="' . htmlspecialchars($txUrl, ENT_QUOTES) . '">Transcribe now</a>';
    }
    echo '</td>';
    echo '</tr>';
  }
  if (empty($rows)) {
    echo '<tr><td colspan="6">No recordings found.</td></tr>';
  }
  echo '</tbody></table>';
  echo '</body></html>';
  exit;
}

if ($__action === 'transcribe') {
  // Access control: mirror recordings protection
  if (recordings_basic_configured()) {
    recordings_basic_enforce();
  } elseif (recordings_password_configured()) {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['recordings_auth_ok'])) {
      http_response_code(403);
      echo 'Forbidden';
      exit;
    }
  } else {
    $tokenParam = (string)($_GET['token'] ?? '');
    $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
    if ($tokenCfg !== '' && !hash_equals($tokenCfg, $tokenParam)) {
      http_response_code(403);
      echo 'Forbidden';
      exit;
    }
  }

  header('Content-Type: text/html; charset=UTF-8');
  $sid = isset($_GET['sid']) ? preg_replace('/[^A-Za-z0-9]/', '', (string)$_GET['sid']) : '';
  $host = $_SERVER['HTTP_HOST'] ?? 'mechanicstaugustine.com';
  $back = 'https://' . $host . '/voice/recording_callback.php?action=recordings';
  $tokenCfg = defined('VOICE_RECORDINGS_TOKEN') ? (string)VOICE_RECORDINGS_TOKEN : '';
  if ($tokenCfg !== '' && !recordings_basic_configured() && !recordings_password_configured()) $back .= '&token=' . rawurlencode($tokenCfg);

  echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Transcribe Recording</title><style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;margin:20px;background:#f8fafc;color:#0f172a} .card{background:#fff;padding:16px;border-radius:10px;box-shadow:0 1px 2px rgba(0,0,0,.06);max-width:720px} a.btn{display:inline-block;margin-top:12px;padding:8px 12px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px}</style></head><body><div class="card">';
  if ($sid === '') {
    echo '<h2>Missing Recording SID</h2><p>Please provide a valid sid parameter.</p>';
    echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
    echo '</div></body></html>';
    exit;
  }
  if (!(defined('TWILIO_TRANSCRIBE_ENABLED') && TWILIO_TRANSCRIBE_ENABLED)) {
    // Try Whisper fallback if configured
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
      $dl = fetch_twilio_recording_mp3($sid);
      if (!empty($dl['ok'])) {
        $wx = whisper_transcribe_bytes((string)$dl['data'], $sid . '.mp3');
        if (!empty($wx['ok'])) {
          // Forward transcript into main handler to index and attach to CRM/log
          $forwardData = [
            'TranscriptionText' => (string)$wx['text'],
            'RecordingSid' => $sid,
            'CI_Source' => 'whisper_fallback'
          ];
          $callbackUrl = 'http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/recording_callback.php';
          $ch = curl_init($callbackUrl);
          curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($forwardData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
          ]);
          $resp = curl_exec($ch);
          $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $errno = curl_errno($ch);
          $err  = curl_error($ch);
          curl_close($ch);
          voice_log_event('whisper_forward', ['sid'=>$sid, 'ok'=>($errno===0 && $http>=200 && $http<300), 'http'=>$http, 'errno'=>$errno, 'error'=>$err]);
          echo '<h2>Transcription Complete</h2><p>We transcribed <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code> using Whisper. Return to the recordings page and refresh to view it.</p>';
          echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
          echo '</div></body></html>';
          exit;
        } else {
          $detail = htmlspecialchars(json_encode($wx), ENT_QUOTES);
          echo '<h2>Whisper Failed</h2><p>OpenAI Whisper did not return a transcript.</p><pre style="white-space:pre-wrap;background:#f1f5f9;padding:8px;border-radius:6px">' . $detail . '</pre>';
          echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
          echo '</div></body></html>';
          exit;
        }
      } else {
        $detail = htmlspecialchars(json_encode($dl), ENT_QUOTES);
        echo '<h2>Download Failed</h2><p>Could not fetch the Twilio recording audio for <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>.</p><pre style="white-space:pre-wrap;background:#f1f5f9;padding:8px;border-radius:6px">' . $detail . '</pre>';
        echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
        echo '</div></body></html>';
        exit;
      }
    }
  } else {
    // Native transcription enabled, but use Whisper for manual "Transcribe now"
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
      $dl = fetch_twilio_recording_mp3($sid);
      if (!empty($dl['ok'])) {
        $wx = whisper_transcribe_bytes((string)$dl['data'], $sid . '.mp3');
        if (!empty($wx['ok'])) {
          // Forward transcript into main handler to index and attach to CRM/log
          $forwardData = [
            'TranscriptionText' => (string)$wx['text'],
            'RecordingSid' => $sid,
            'CI_Source' => 'whisper_fallback'
          ];
          $callbackUrl = 'http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/recording_callback.php';
          $ch = curl_init($callbackUrl);
          curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($forwardData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
          ]);
          $resp = curl_exec($ch);
          $respHttp = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $respErr = curl_error($ch);
          curl_close($ch);
          voice_log_event('whisper_forward', ['sid'=>$sid, 'ok'=>$respHttp>=200&&$respHttp<300, 'http'=>$respHttp, 'errno'=>curl_errno($ch), 'error'=>$respErr]);
          echo '<h2>Transcript Generated</h2><p>Successfully transcribed <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code> using Whisper and attached to CRM lead.</p>';
          echo '<div style="background:#f1f5f9;padding:12px;border-radius:6px;margin:16px 0;border-left:4px solid #059669"><strong>Transcript:</strong><br>' . nl2br(htmlspecialchars((string)$wx['text'], ENT_QUOTES)) . '</div>';
        } else {
          $detail = htmlspecialchars(json_encode($wx), ENT_QUOTES);
          echo '<h2>Whisper Failed</h2><p>Transcription failed for <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>.</p><pre style="white-space:pre-wrap;background:#f1f5f9;padding:8px;border-radius:6px">' . $detail . '</pre>';
        }
      } else {
        $detail = htmlspecialchars(json_encode($dl), ENT_QUOTES);
        echo '<h2>Download Failed</h2><p>Could not fetch the Twilio recording audio for <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>.</p><pre style="white-space:pre-wrap;background:#f1f5f9;padding:8px;border-radius:6px">' . $detail . '</pre>';
      }
    } else {
      echo '<h2>Transcription Not Configured</h2><p>Neither Twilio native transcription (TWILIO_TRANSCRIBE_ENABLED) nor OpenAI (OPENAI_API_KEY) is configured, so we cannot transcribe <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>.</p>';
    }
    echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
    echo '</div></body></html>';
    exit;
  }
  $res = request_ci_transcript($sid);
  voice_log_event('transcribe_enqueue', ['sid'=>$sid, 'result'=>$res]);
  if (!empty($res['ok'])) {
    echo '<h2>Transcript Enqueued</h2><p>We requested a transcript for <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>. Refresh the recordings page in a minute to see it.</p>';
  } else {
    $detail = htmlspecialchars(json_encode($res), ENT_QUOTES);
    echo '<h2>Failed to Enqueue</h2><p>Could not enqueue transcript for <code>' . htmlspecialchars($sid, ENT_QUOTES) . '</code>.</p><pre style="white-space:pre-wrap;background:#f1f5f9;padding:8px;border-radius:6px">' . $detail . '</pre>';
  }
  echo '<a class="btn" href="' . htmlspecialchars($back, ENT_QUOTES) . '">Back to recordings</a>';
  echo '</div></body></html>';
  exit;
}

if ($__action === 'dial') {
  // Handle Twilio <Dial action="..."> callback; on failure, create a minimal missed-call lead.
  $status = strtolower(trim((string)($_POST['DialCallStatus'] ?? '')));
  $from = (string)($_POST['From'] ?? $_POST['Caller'] ?? '');
  $to = (string)($_POST['To'] ?? $_POST['Called'] ?? '');
  $callSid = (string)($_POST['CallSid'] ?? '');
  $now = date('c');
  $event = ['status' => $status, 'from' => $from, 'to' => $to, 'callSid' => $callSid];

  $failed = in_array($status, ['failed','busy','no-answer','canceled'], true);
  if ($failed) {
    // Create a minimal CRM lead for missed call
    $last4 = '';
    if ($from && preg_match('/(\d{4})$/', preg_replace('/[^\d\+]/', '', $from), $m4)) { $last4 = $m4[1]; }
    $leadData = [
      'phone' => $from,
      'recording_url' => '',
      'transcript' => 'Missed call to ' . $to . ' at ' . $now,
      'source' => 'Phone',
      'created_at' => $now,
      '_bypass_dedupe' => true,
      'first_name' => 'Missed',
      'last_name' => $last4 ? ('Caller ' . $last4) : 'Caller',
      'name' => 'Missed ' . ($last4 ? ('Caller ' . $last4) : 'Caller')
    ];
    $crmResult = create_crm_lead($leadData);
    $event['missed_lead'] = $leadData;
    $event['crm_result'] = $crmResult;
  }

  voice_log_event('dial_action', $event);
  header('Content-Type: text/xml');
  echo '<?xml version="1.0" encoding="UTF-8"?>';
  echo '<Response>';
  if ($failed) {
    echo '<Say voice="alice">Sorry, we could not connect you right now. We\'ll call you back shortly.</Say>';
  }
  echo '</Response>';
  exit;
}

// When included by other handlers (e.g., dial_result.php), allow library-only mode
if (!defined('VOICE_LIB_ONLY')) {
  header('Content-Type: application/json');
}

function respond($data, int $code=200){ http_response_code($code); echo json_encode($data, JSON_UNESCAPED_SLASHES); exit; }
function log_line(array $row): void {
  $line = json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
  $path = __DIR__ . '/voice.log';
  $ok = @file_put_contents($path, $line, FILE_APPEND);
  if ($ok === false) {
    error_log('VOICE_LOG_WRITE_FAIL path=' . $path);
    error_log('VOICE_EVENT ' . $line);
  }
}

/**
 * Request a Twilio Conversational Intelligence transcript for a given Recording SID.
 * Requires CI_SERVICE_SID and either (TWILIO_ACCOUNT_SID+TWILIO_AUTH_TOKEN) or (TWILIO_API_KEY_SID+TWILIO_API_KEY_SECRET)
 */
function request_ci_transcript(string $recordingSid): array {
  $serviceSid = defined('CI_SERVICE_SID') ? (string)CI_SERVICE_SID : '';
  if ($serviceSid === '') return ['ok'=>false,'error'=>'no_service_sid'];

  $authSid = defined('TWILIO_API_KEY_SID') && TWILIO_API_KEY_SID ? (string)TWILIO_API_KEY_SID : (defined('TWILIO_ACCOUNT_SID') ? (string)TWILIO_ACCOUNT_SID : '');
  $authSecret = defined('TWILIO_API_KEY_SECRET') && TWILIO_API_KEY_SECRET ? (string)TWILIO_API_KEY_SECRET : (defined('TWILIO_AUTH_TOKEN') ? (string)TWILIO_AUTH_TOKEN : '');
  if ($authSid === '' || $authSecret === '') return ['ok'=>false,'error'=>'no_auth'];

  $url = 'https://intelligence.twilio.com/v2/Transcripts';
  $post = [
    'ServiceSid' => $serviceSid,
    'Channel'    => json_encode(['media_properties'=>['source_sid'=>$recordingSid]], JSON_UNESCAPED_SLASHES),
  ];
  // Optional explicit webhook override
  if (defined('CI_WEBHOOK_URL') && CI_WEBHOOK_URL) {
    $post['WebhookUrl'] = (string)CI_WEBHOOK_URL;
  }
  
  // Debug logging
  error_log('CI_TRANSCRIPT_REQUEST: serviceSid=' . $serviceSid . ' recordingSid=' . $recordingSid . ' post=' . json_encode($post));
  
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_USERPWD => $authSid . ':' . $authSecret,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 8,
  ]);
  $resp = curl_exec($ch);
  $errno = curl_errno($ch);
  $err  = curl_error($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ['ok'=>($errno===0 && $http>=200 && $http<300), 'http'=>$http, 'curl_errno'=>$errno, 'curl_error'=>$err, 'body'=>$resp];
}

/**
 * Fetch Twilio recording MP3 by SID using Account SID/Auth Token
 */
function fetch_twilio_recording_mp3(string $recordingSid): array {
  $acct = defined('TWILIO_ACCOUNT_SID') ? (string)TWILIO_ACCOUNT_SID : '';
  $auth = defined('TWILIO_AUTH_TOKEN') ? (string)TWILIO_AUTH_TOKEN : '';
  if ($acct === '' || $auth === '') return ['ok'=>false,'error'=>'no_twilio_creds'];
  $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($acct) . '/Recordings/' . rawurlencode($recordingSid) . '.mp3';
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_USERPWD => $acct . ':' . $auth,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
  ]);
  $bin = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errno = curl_errno($ch);
  $err  = curl_error($ch);
  curl_close($ch);
  return ['ok'=>($errno===0 && $http>=200 && $http<300 && $bin!==false), 'http'=>$http, 'curl_errno'=>$errno, 'curl_error'=>$err, 'data'=>$bin];
}

/**
 * Transcribe audio bytes with OpenAI Whisper
 */
function whisper_transcribe_bytes(string $audioBytes, string $filename = 'call.mp3'): array {
  if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) return ['ok'=>false,'error'=>'no_openai_key'];
  $boundary = '----copilot-' . bin2hex(random_bytes(8));
  $eol = "\r\n";
  $body = '';
  // model
  $body .= '--' . $boundary . $eol;
  $body .= 'Content-Disposition: form-data; name="model"' . $eol . $eol;
  $body .= 'whisper-1' . $eol;
  // file
  $body .= '--' . $boundary . $eol;
  $body .= 'Content-Disposition: form-data; name="file"; filename="' . addslashes($filename) . '"' . $eol;
  $body .= 'Content-Type: audio/mpeg' . $eol . $eol;
  $body .= $audioBytes . $eol;
  $body .= '--' . $boundary . '--' . $eol;

  $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . OPENAI_API_KEY,
      'Content-Type: multipart/form-data; boundary=' . $boundary,
    ],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
  ]);
  $resp = curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errno = curl_errno($ch);
  $err  = curl_error($ch);
  curl_close($ch);
  if ($errno !== 0 || $http < 200 || $http >= 300 || $resp === false) {
    return ['ok'=>false,'http'=>$http,'curl_errno'=>$errno,'curl_error'=>$err,'body'=>$resp];
  }
  $j = json_decode((string)$resp, true);
  if (!is_array($j) || empty($j['text'])) return ['ok'=>false,'error'=>'invalid_response','body'=>$resp];
  return ['ok'=>true,'text'=>(string)$j['text']];
}


// If included as library, don't run the request handler
if (defined('VOICE_LIB_ONLY') && VOICE_LIB_ONLY) {
  return; // expose functions only
}

$in = $_POST; // Twilio sends application/x-www-form-urlencoded
$now = date('c');
$log = [ 'ts'=>$now, 'ip'=>($_SERVER['REMOTE_ADDR'] ?? ''), 'post'=>$in ];

$recordingUrl = $in['RecordingUrl'] ?? '';
$transcriptText = $in['TranscriptionText'] ?? '';
$recordingSid = $in['RecordingSid'] ?? '';
$from = $in['From'] ?? $in['Caller'] ?? '';
$to   = $in['To'] ?? $in['Called'] ?? '';
$callSid = $in['CallSid'] ?? '';
$duration = (int)($in['RecordingDuration'] ?? 0);

// Testing aid: allow bypassing phone-based dedupe via flag
$bypassDedupe = false;
if (isset($in['BypassDedupe'])) {
  $v = strtolower(trim((string)$in['BypassDedupe']));
  $bypassDedupe = ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'on');
} elseif (isset($in['dedupe'])) {
  $v = strtolower(trim((string)$in['dedupe']));
  // e.g., dedupe=off/false/no -> bypass
  $bypassDedupe = ($v === 'off' || $v === 'false' || $v === 'no' || $v === '0');
}
if ($bypassDedupe) { $log['dedupe_bypass'] = true; }

// Handle both recording callbacks and transcription-only callbacks
$transcript = '';
if (!empty($transcriptText)) {
  $transcript = $transcriptText;
} elseif (!empty($in['TranscriptionText'])) {
  $transcript = $in['TranscriptionText'];
}

// If From/To missing but we have CallSid, try to fetch call details from Twilio to enrich
if (($from === '' || $to === '') && $callSid && defined('TWILIO_ACCOUNT_SID') && defined('TWILIO_AUTH_TOKEN')) {
  try {
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode((string)TWILIO_ACCOUNT_SID) . '/Calls/' . rawurlencode($callSid) . '.json';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_USERPWD => (string)TWILIO_ACCOUNT_SID . ':' . (string)TWILIO_AUTH_TOKEN,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_CONNECTTIMEOUT => 6,
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $cj = json_decode((string)$resp, true);
    if ($http === 200 && is_array($cj)) {
      if ($from === '' && !empty($cj['from'])) $from = (string)$cj['from'];
      if ($to === '' && !empty($cj['to'])) $to = (string)$cj['to'];
    }
  } catch (Throwable $e) {
    // ignore; best-effort enrichment
  }
}

$log['summary'] = [ 'from'=>$from, 'to'=>$to, 'duration'=>$duration, 'url'=>$recordingUrl, 'transcript'=>$transcript ];

// Early transcript quality checks - reject obvious junk before processing
if ($transcript) {
  $trimmed = trim($transcript);
  
  // Skip very short transcripts, but if we have a recording, still create a minimal lead
  if (strlen($trimmed) < 15) {
    $log['skipped'] = 'transcript_too_short';
    if ($recordingUrl) {
      $playableRecordingUrl = $recordingUrl;
      if (preg_match('/Recordings\/([^\/]+)$/', $recordingUrl, $matches)) {
        $recSid = $matches[1];
  $playableRecordingUrl = "https://mechanicstaugustine.com/voice/recording_callback.php?action=download&sid=" . $recSid;
      }
      $last4 = '';
      if ($from && preg_match('/(\d{4})$/', preg_replace('/[^\d\+]/', '', $from), $m4)) { $last4 = $m4[1]; }
      $leadData = [
        'phone' => $from,
        'recording_url' => $playableRecordingUrl,
        'transcript' => '',
        'source' => 'Phone',
        'created_at' => $now,
        '_bypass_dedupe' => true,
        'first_name' => 'Unknown',
        'last_name' => $last4 ? ('Caller ' . $last4) : 'Caller',
        'name' => 'Unknown ' . ($last4 ? ('Caller ' . $last4) : 'Caller')
      ];
      $crmResult = create_crm_lead($leadData);
      $log['minimal_lead'] = true;
      $log['crm_lead'] = $leadData;
      $log['crm_result'] = $crmResult;
    }
    log_line($log);
    respond(['ok'=>true, 'skipped'=>'transcript_too_short']);
  }
  
  // Check for common junk transcript patterns that indicate system prompts/voicemail
  $junk_patterns = [
    '/^(so|the|fast|pickup|we|ask|you|to|let|thank|you)\s+/i',      // Voicemail prompts
    '/^(press|dial|your|call|is|please|wait|hold|enter)\s+/i',       // System prompts
    '/^(hello|hi|hey|goodbye|bye)\s*$/i',                            // Just greetings
    '/^(yes|no|ok|sure|yeah|maybe|probably|possibly)\s*$/i',         // Single word responses
    '/^(um|uh|ah|er|hmm|well)\s+/i',                                // Filler words
    '/^(one|two|three|four|five|six|seven|eight|nine|zero)[\s\d]*$/i', // Just numbers
    '/voicemail|mailbox|beep|tone|message|unavailable|busy/i',       // Voicemail system
    '/not available|please try|call back|after the/i',              // Common system phrases
    '/in case of.*pickup.*we ask.*let.*know/i',                     // Specific pickup message
    '/fast pickup.*we ask/i',                                        // Pickup variations
    '/^in case of a fast pickup.*$/i',                               // Exact fast pickup message
  ];
  
  foreach ($junk_patterns as $pattern) {
    if (preg_match($pattern, $trimmed)) {
      $log['skipped'] = 'junk_transcript';
      $log['junk_pattern'] = $pattern;
      if ($recordingUrl) {
        $playableRecordingUrl = $recordingUrl;
        if (preg_match('/Recordings\/([^\/]+)$/', $recordingUrl, $matches)) {
          $recSid = $matches[1];
          $playableRecordingUrl = "https://mechanicstaugustine.com/voice/recording_callback.php?action=download&sid=" . $recSid;
        }
        $last4 = '';
        if ($from && preg_match('/(\d{4})$/', preg_replace('/[^\d\+]/', '', $from), $m4)) { $last4 = $m4[1]; }
        $leadData = [
          'phone' => $from,
          'recording_url' => $playableRecordingUrl,
          'transcript' => '',
          'source' => 'Phone',
          'created_at' => $now,
          '_bypass_dedupe' => true,
          'first_name' => 'Unknown',
          'last_name' => $last4 ? ('Caller ' . $last4) : 'Caller',
          'name' => 'Unknown ' . ($last4 ? ('Caller ' . $last4) : 'Caller')
        ];
        $crmResult = create_crm_lead($leadData);
        $log['minimal_lead'] = true;
        $log['crm_lead'] = $leadData;
        $log['crm_result'] = $crmResult;
      }
      log_line($log);
      respond(['ok'=>true, 'skipped'=>'junk_transcript', 'pattern'=>$pattern]);
    }
  }
}

// AUTO-TRANSCRIBE: If we received a recording without transcript, automatically transcribe with Whisper
$log['debug_auto_transcribe'] = [
  'transcript_empty' => ($transcript === ''),
  'has_recording_sid' => !empty($recordingSid),
  'openai_defined' => defined('OPENAI_API_KEY'),
  'openai_value' => defined('OPENAI_API_KEY') ? (OPENAI_API_KEY ? 'yes' : 'empty') : 'undefined'
];
if ($transcript === '' && $recordingSid && defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
  $dl = fetch_twilio_recording_mp3($recordingSid);
  if (!empty($dl['ok'])) {
    $wx = whisper_transcribe_bytes((string)$dl['data'], $recordingSid . '.mp3');
    if (!empty($wx['ok'])) {
      $transcript = (string)$wx['text'];
      $log['auto_whisper'] = ['ok' => true, 'length' => strlen($transcript)];
      // Continue processing with the transcript we just generated
    } else {
      $log['auto_whisper'] = ['ok' => false, 'error' => $wx];
    }
  } else {
    $log['auto_whisper'] = ['ok' => false, 'download_error' => $dl];
  }
}

// --- CRM Integration ---
// Handle both recording+transcription and transcription-only callbacks
if (($recordingUrl && $transcript) || (!$recordingUrl && $transcript)) {
  // Start with NLP extraction from transcript
  $extracted = extract_customer_data($transcript);

  // Merge any pre-extracted hints posted by upstream (e.g., CI Operator Results)
  foreach (['first_name','last_name','name','year','make','model','engine_size','address','notes'] as $k) {
    if (isset($in[$k]) && is_string($in[$k]) && trim((string)$in[$k]) !== '') {
      $extracted[$k] = trim((string)$in[$k]);
    }
  }

  // Enrich with call context
  $playableRecordingUrl = $recordingUrl;
  if ($recordingUrl && preg_match('/Recordings\/([^\/]+)$/', $recordingUrl, $matches)) {
    $recordingSid = $matches[1];
  $playableRecordingUrl = "https://mechanicstaugustine.com/voice/recording_callback.php?action=download&sid=" . $recordingSid;
  }
  
  $leadData = [
    'phone'         => $from,
    'recording_url' => $playableRecordingUrl ?: 'transcription-only',
    'transcript'    => $transcript,
    'source'        => 'Phone',
    'created_at'    => $now,
    '_bypass_dedupe'=> $bypassDedupe,
  ];

  $leadData = array_merge($leadData, $extracted);

  // Decide whether to create a lead
  $hasName = !empty($leadData['first_name']) || !empty($leadData['last_name']) || !empty($leadData['name']);
  $hasPhone = false;
  if (!empty($leadData['phone'])) {
    $pn = preg_replace('/[^\d\+]/', '', (string)$leadData['phone']);
    if (preg_match('/\d{7,}/', $pn)) $hasPhone = true;
    $leadData['phone'] = $pn; // normalize for storage
  }

  // Skip noisy/minimal leads unless we have at least a phone or a name; also skip if voicemail was flagged upstream
  $voicemail = !empty($in['voicemail_detected']);
  $shortTranscript = strlen((string)$transcript) < 16;
  if ($voicemail || (!$hasName && !$hasPhone && $shortTranscript)) {
    $log['skipped_unqualified'] = true;
    log_line($log);
    respond(['ok'=>true]);
  }

  if (!$hasName && $hasPhone) {
    // Create a minimal, but distinguishable lead using phone suffix
    $last4 = '';
    if (preg_match('/(\d{4})$/', (string)$leadData['phone'], $m4)) { $last4 = $m4[1]; }
    $leadData['first_name'] = 'Unknown';
    $leadData['last_name']  = $last4 ? ('Caller ' . $last4) : 'Caller';
    $leadData['name'] = $leadData['first_name'] . ' ' . $leadData['last_name'];
    $log['minimal_lead'] = true;
  }

  $crmResult = create_crm_lead($leadData);
  $log['crm_lead'] = $leadData;
  $log['crm_result'] = $crmResult;

  // Post-create: email notification (SMTP when configured, else mail())
  try {
    if (defined('VOICE_EMAIL_NOTIFY_TO') && VOICE_EMAIL_NOTIFY_TO) {
      $to = (string)VOICE_EMAIL_NOTIFY_TO;
      $subjTpl = defined('VOICE_EMAIL_SUBJECT') ? (string)VOICE_EMAIL_SUBJECT : '[New Phone Lead]';
      $name = trim((string)($leadData['name'] ?? (($leadData['first_name'] ?? '') . ' ' . ($leadData['last_name'] ?? ''))));
      $phone = (string)($leadData['phone'] ?? '');
      $subj = str_replace(['{{name}}','{{phone}}'], [$name, $phone], $subjTpl);

      $lines = [];
      $lines[] = 'New phone lead received:';
      $lines[] = 'Name: ' . ($name ?: '(unknown)');
      $lines[] = 'Phone: ' . ($phone ?: '(missing)');
      foreach (['year','make','model','address','notes'] as $k) {
        if (!empty($leadData[$k])) $lines[] = ucfirst($k) . ': ' . (string)$leadData[$k];
      }
      if (!empty($leadData['recording_url'])) $lines[] = 'Recording: ' . (string)$leadData['recording_url'];
      // If DB fallback returned id, include item link
      $itemId = null;
      if (is_array($crmResult) && isset($crmResult['fallback']['id']) && $crmResult['fallback']['id']) {
        $itemId = (int)$crmResult['fallback']['id'];
      }
      $crmLink = '';
      if ($itemId && defined('CRM_LEADS_ENTITY_ID')) {
        $crmLink = 'https://mechanicstaugustine.com/crm/index.php?module=items/items&path=' . (int)CRM_LEADS_ENTITY_ID . '&id=' . $itemId;
        $lines[] = 'Open in CRM: ' . $crmLink;
      }
      $body = implode("\n", $lines) . "\n";

      $from = defined('VOICE_SMTP_FROM') && VOICE_SMTP_FROM ? (string)VOICE_SMTP_FROM : (defined('VOICE_EMAIL_FROM') ? (string)VOICE_EMAIL_FROM : '');
      $sent = false;
      $meta = ['to'=>$to,'subject'=>$subj,'crm_link'=>$crmLink,'method'=>'mail'];

      if (defined('VOICE_SENDGRID_API_KEY') && VOICE_SENDGRID_API_KEY) {
        $meta['method'] = 'sendgrid';
        $fromUse = $from ?: (defined('VOICE_EMAIL_FROM') ? (string)VOICE_EMAIL_FROM : 'no-reply@localhost');
        $sent = sendgrid_send_plain((string)VOICE_SENDGRID_API_KEY, $fromUse, $to, $subj, $body);
        $meta['from'] = $fromUse;
      } elseif (defined('VOICE_SMTP_HOST') && VOICE_SMTP_HOST) {
        $meta['method'] = 'smtp';
        $sent = smtp_send_plain(
          (string)VOICE_SMTP_HOST,
          (int)(defined('VOICE_SMTP_PORT') ? VOICE_SMTP_PORT : 587),
          (string)(defined('VOICE_SMTP_SECURE') ? VOICE_SMTP_SECURE : 'tls'),
          (string)(defined('VOICE_SMTP_USERNAME') ? VOICE_SMTP_USERNAME : ''),
          (string)(defined('VOICE_SMTP_PASSWORD') ? VOICE_SMTP_PASSWORD : ''),
          $from,
          $to,
          $subj,
          $body
        );
        $meta['from'] = $from ?: (defined('VOICE_SMTP_USERNAME') ? (string)VOICE_SMTP_USERNAME : '');
      } else {
        $headers = [];
        if ($from) $headers[] = 'From: ' . $from;
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $sent = @mail($to, $subj, $body, implode("\r\n", $headers));
        $meta['from'] = $from;
      }
      $meta['ok'] = $sent;
      $log['email_notify'] = $meta;
    }
  } catch (Throwable $e) {
    $log['email_notify_error'] = $e->getMessage();
  }
} else {
  // We have a recording but no transcript. Create a minimal CRM lead so the call is visible.
  if ($recordingUrl && !$transcript) {
    $playableRecordingUrl = $recordingUrl;
    if (preg_match('/Recordings\/([^\/]+)$/', $recordingUrl, $matches)) {
      $recSid = $matches[1];
  $playableRecordingUrl = "https://mechanicstaugustine.com/voice/recording_callback.php?action=download&sid=" . $recSid;
    }

    $last4 = '';
    if ($from && preg_match('/(\d{4})$/', preg_replace('/[^\d\+]/', '', $from), $m4)) { $last4 = $m4[1]; }

    $leadData = [
      'phone' => $from,
      'recording_url' => $playableRecordingUrl,
      'transcript' => '',
      'source' => 'Phone',
      'created_at' => $now,
      '_bypass_dedupe' => true,
      'first_name' => 'Unknown',
      'last_name' => $last4 ? ('Caller ' . $last4) : 'Caller',
      'name' => 'Unknown ' . ($last4 ? ('Caller ' . $last4) : 'Caller')
    ];
    $crmResult = create_crm_lead($leadData);
    $log['minimal_lead'] = true;
    $log['crm_lead'] = $leadData;
    $log['crm_result'] = $crmResult;
  } else {
    if (!$transcript) {
      $log['skip_reason'] = 'No transcript available';
    } else {
      $log['skip_reason'] = 'No recording URL or transcript available';
    }
  }
}

log_line($log);
respond(['ok'=>true]);

// Minimal SMTP sender supporting STARTTLS/SSL and basic AUTH LOGIN
function smtp_send_plain(string $host, int $port, string $secure, string $username, string $password, string $from, string $to, string $subject, string $body): bool {
  $prefix = '';
  if (strtolower($secure) === 'ssl') $prefix = 'ssl://';
  $fp = @fsockopen($prefix . $host, $port, $eno, $estr, 10);
  if (!$fp) return false;
  $read = function() use ($fp) {
    $resp = '';
    while (!feof($fp)) {
      $line = fgets($fp, 512);
      if ($line === false) break;
      $resp .= $line;
      if (strlen($line) < 4 || substr($line, 3, 1) !== '-') break;
    }
    return $resp;
  };
  $write = function(string $s) use ($fp) { fwrite($fp, $s . "\r\n"); };
  $r = $read(); if (strpos($r, '220') !== 0) { fclose($fp); return false; }
  $write('EHLO localhost'); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return false; }
  if (strtolower($secure) === 'tls') {
    $write('STARTTLS'); $r = $read(); if (strpos($r, '220') !== 0) { fclose($fp); return false; }
    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
    $write('EHLO localhost'); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return false; }
  }
  if ($username !== '') {
    $write('AUTH LOGIN'); $r = $read(); if (strpos($r, '334') !== 0) { fclose($fp); return false; }
    $write(base64_encode($username)); $r = $read(); if (strpos($r, '334') !== 0) { fclose($fp); return false; }
    $write(base64_encode($password)); $r = $read(); if (strpos($r, '235') !== 0) { fclose($fp); return false; }
  }
  $fromAddr = $from !== '' ? $from : $username;
  $write('MAIL FROM:<' . $fromAddr . '>'); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return false; }
  $write('RCPT TO:<' . $to . '>'); $r = $read(); if (strpos($r, '250') !== 0 && strpos($r, '251') !== 0) { fclose($fp); return false; }
  $write('DATA'); $r = $read(); if (strpos($r, '354') !== 0) { fclose($fp); return false; }
  $headers = [
    'From: ' . $fromAddr,
    'To: ' . $to,
    'Subject: ' . $subject,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
  ];
  $msg = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
  $write($msg); $r = $read(); if (strpos($r, '250') !== 0) { fclose($fp); return false; }
  $write('QUIT');
  fclose($fp);
  return true;
}

// Minimal SendGrid sender via HTTP API
function sendgrid_send_plain(string $apiKey, string $from, string $to, string $subject, string $body): bool {
  if ($apiKey === '' || $to === '') return false;
  $url = 'https://api.sendgrid.com/v3/mail/send';
  $payload = [
    'personalizations' => [[ 'to' => [[ 'email' => $to ]] ]],
    'from' => [ 'email' => $from ?: 'no-reply@localhost' ],
    'subject' => $subject,
    'content' => [[ 'type' => 'text/plain', 'value' => $body ]],
  ];
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . $apiKey,
      'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 8,
  ]);
  curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errno = curl_errno($ch);
  curl_close($ch);
  return ($errno === 0 && $http >= 200 && $http < 300); // SendGrid returns 202 on success
}

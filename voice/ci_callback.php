<?php
declare(strict_types=1);

/**
 * Twilio Conversational Intelligence (CI) Webhook Endpoint
 * 
 * Receives JSON payloads from CI Service containing transcript data.
 * Extracts transcript text and forwards to recording_callback.php as TranscriptionText.
 */

header('Content-Type: application/json');

$env = __DIR__ . '/../api/.env.local.php';
if (is_file($env)) {
  require $env;
}

function log_line(array $row): void {
  $line = json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
  $path = __DIR__ . '/voice.log';
  $ok = @file_put_contents($path, $line, FILE_APPEND);
  if ($ok === false) {
    error_log('VOICE_LOG_WRITE_FAIL path=' . $path);
    error_log('CI_EVENT ' . $line);
  }
}

// Helper: determine current base URL (scheme + host) for building absolute links/callbacks
function ci_base_url(): string {
  $host = $_SERVER['HTTP_HOST'] ?? 'mechanicstaugustine.com';
  $scheme = 'https';
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $scheme = (string)$_SERVER['HTTP_X_FORWARDED_PROTO'];
  } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
    $scheme = (string)$_SERVER['REQUEST_SCHEME'];
  } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $scheme = 'https';
  } else {
    $scheme = 'http';
  }
  return $scheme . '://' . $host;
}

/**
 * Recursively search for transcript text in CI JSON payload
 */
function extract_transcript_text(array $data): string {
  // Common CI transcript fields
  $candidates = ['transcript', 'text', 'content', 'transcription_text', 'media_transcript'];
  
  foreach ($candidates as $key) {
    if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
      return trim($data[$key]);
    }
  }
  
  // Search nested structures
  foreach ($data as $value) {
    if (is_array($value)) {
      $found = extract_transcript_text($value);
      if ($found !== '') return $found;
    }
  }
  
  return '';
}

// Parse JSON input
$input = file_get_contents('php://input');
$json = json_decode($input, true);

$now = date('c');
$log = [
  'ts' => $now,
  'type' => 'ci_webhook',
  'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
  'raw_input' => $input,
  'parsed_json' => $json
];

if (!is_array($json)) {
  $log['error'] = 'invalid_json';
  log_line($log);
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
  exit;
}

// Try to extract transcript directly, else fetch via API if transcript_sid present
$transcript = extract_transcript_text($json);
$transcriptSid = '';
if (isset($json['transcript_sid']) && is_string($json['transcript_sid'])) {
  $transcriptSid = trim($json['transcript_sid']);
}

// If no transcript text in payload, attempt to fetch sentences from Twilio Intelligence API
if ($transcript === '' && $transcriptSid !== '') {
  $authSid = defined('TWILIO_API_KEY_SID') && TWILIO_API_KEY_SID ? (string)TWILIO_API_KEY_SID : (defined('TWILIO_ACCOUNT_SID') ? (string)TWILIO_ACCOUNT_SID : '');
  $authSecret = defined('TWILIO_API_KEY_SECRET') && TWILIO_API_KEY_SECRET ? (string)TWILIO_API_KEY_SECRET : (defined('TWILIO_AUTH_TOKEN') ? (string)TWILIO_AUTH_TOKEN : '');
  if ($authSid !== '' && $authSecret !== '') {
    $url = 'https://intelligence.twilio.com/v2/Transcripts/' . rawurlencode($transcriptSid) . '/Sentences';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $authSid . ':' . $authSecret,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    $sentences = [];
    if ($errno === 0 && $http >= 200 && $http < 300) {
      $sj = json_decode((string)$resp, true);
      if (is_array($sj)) {
        // Twilio returns { sentences: [ {text: '...'}, ... ] }
        $arr = $sj['sentences'] ?? ($sj['data'] ?? []);
        if (is_array($arr)) {
          foreach ($arr as $s) {
            if (is_array($s) && !empty($s['text'])) { $sentences[] = trim((string)$s['text']); }
          }
        }
      }
    }
    if (!empty($sentences)) {
      $transcript = trim(implode(' ', $sentences));
      $log['fetched_from_api'] = ['transcript_sid'=>$transcriptSid, 'raw_length'=>strlen((string)$resp), 'sanitized_length'=>strlen($transcript)];
    } else {
      $log['fetched_from_api_error'] = ['http'=>$http, 'curl_errno'=>$errno, 'curl_error'=>$err];
    }
  } else {
    $log['fetched_from_api_error'] = ['error'=>'no_auth'];
  }
}

$log['extracted_transcript'] = $transcript;

// Try to extract additional insights if present in payload
$insights = [];
// summary
if (isset($json['summary']) && is_string($json['summary']) && trim($json['summary']) !== '') {
  $insights['summary'] = trim($json['summary']);
} elseif (isset($json['insights']['summary']) && is_string($json['insights']['summary'])) {
  $insights['summary'] = trim($json['insights']['summary']);
} elseif (isset($json['conversation_summary']) && is_string($json['conversation_summary'])) {
  $insights['summary'] = trim($json['conversation_summary']);
}
// sentiment
if (isset($json['sentiment']) && is_string($json['sentiment'])) {
  $insights['sentiment'] = trim($json['sentiment']);
} elseif (isset($json['insights']['sentiment'])) {
  $s = $json['insights']['sentiment'];
  if (is_string($s)) { $insights['sentiment'] = trim($s); }
  elseif (is_array($s) && isset($s['overall'])) { $insights['sentiment'] = (string)$s['overall']; }
}
// topics (array -> comma string)
if (isset($json['topics']) && is_array($json['topics'])) {
  $topics = array_filter(array_map(function($t){ return is_string($t) ? trim($t) : (is_array($t)&&isset($t['name'])?trim((string)$t['name']):''); }, $json['topics']));
  if (!empty($topics)) $insights['topics'] = implode(', ', $topics);
} elseif (isset($json['insights']['topics']) && is_array($json['insights']['topics'])) {
  $topics = array_filter(array_map(function($t){ return is_string($t) ? trim($t) : (is_array($t)&&isset($t['name'])?trim((string)$t['name']):''); }, $json['insights']['topics']));
  if (!empty($topics)) $insights['topics'] = implode(', ', $topics);
}
// action items (array -> bullets)
if (isset($json['action_items']) && is_array($json['action_items'])) {
  $items = array_filter(array_map(function($a){ return is_string($a) ? trim($a) : (is_array($a)&&isset($a['text'])?trim((string)$a['text']):''); }, $json['action_items']));
  if (!empty($items)) $insights['action_items'] = implode("\n", $items);
} elseif (isset($json['insights']['action_items']) && is_array($json['insights']['action_items'])) {
  $items = array_filter(array_map(function($a){ return is_string($a) ? trim($a) : (is_array($a)&&isset($a['text'])?trim((string)$a['text']):''); }, $json['insights']['action_items']));
  if (!empty($items)) $insights['action_items'] = implode("\n", $items);
}
// intent & urgency if present
foreach (['intent','urgency'] as $k) {
  if (isset($json[$k]) && is_string($json[$k]) && trim($json[$k]) !== '') $insights[$k] = trim($json[$k]);
  elseif (isset($json['insights'][$k]) && is_string($json['insights'][$k])) $insights[$k] = trim($json['insights'][$k]);
}

// Prepare data for recording_callback.php
$forwardData = [
  'TranscriptionText' => $transcript,
  'CI_Source' => 'webhook'
];

// Include call metadata if available
if (isset($json['from'])) $forwardData['From'] = $json['from'];
if (isset($json['to'])) $forwardData['To'] = $json['to'];
if (isset($json['recording_sid'])) $forwardData['RecordingSid'] = $json['recording_sid'];
if ($transcriptSid) $forwardData['TranscriptSid'] = $transcriptSid;
// Forward any extracted insights
foreach ($insights as $k => $v) { $forwardData[$k] = $v; }

// Forward to recording callback handler
$callbackUrl = ci_base_url() . dirname($_SERVER['REQUEST_URI']) . '/recording_callback.php';
$ch = curl_init($callbackUrl);
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query($forwardData),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_CONNECTTIMEOUT => 10,
]);

$resp = curl_exec($ch);
$errno = curl_errno($ch);
$err = curl_error($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$log['forward'] = [
  'url' => $callbackUrl,
  'data' => $forwardData,
  'http' => $http,
  'curl_errno' => $errno,
  'curl_error' => $err,
  'response' => $resp
];

log_line($log);

if ($errno !== 0 || $http < 200 || $http >= 300) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'forward_failed', 'details' => $log['forward']]);
} else {
  http_response_code(200);
  echo json_encode(['ok' => true, 'forwarded' => true]);
}

<?php
declare(strict_types=1);
header('Content-Type: text/xml');

// Load shared config (CRM/Twilio values live here)
$env = __DIR__ . '/../api/.env.local.php';
if (is_file($env)) {
  require $env;
}

$host = $_SERVER['HTTP_HOST'] ?? 'mechanicstaugustine.com';
// Determine scheme from proxy headers/environment to build proper absolute callbacks
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
$base = $scheme . '://' . $host;
$callback = $base . '/voice/recording_callback.php';
// Destination number: prefer env, fallback to test number
$to = defined('TWILIO_FORWARD_TO') && TWILIO_FORWARD_TO ? (string)TWILIO_FORWARD_TO : '+19046634789';
$to = preg_replace('/[^0-9\+]/', '', $to);

// Log webhook hit and dial target for diagnostics
try {
  $logFile = __DIR__ . '/voice.log';
  $entry = [
    'ts' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'event' => 'incoming_twiml',
    'to' => $to,
    'from' => $_POST['From'] ?? $_GET['From'] ?? null,
    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
  ];
  @file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
} catch (\Throwable $e) {
  // ignore logging errors
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Response>
  <!-- Quick audible marker so we know the webhook responded -->
  <Say voice="alice">Connecting you now.</Say>
  <Pause length="1" />
  <Dial record="record-from-answer"
        answerOnBridge="true"
      callerId="<?=htmlspecialchars((defined('TWILIO_CALLER_ID') && TWILIO_CALLER_ID) ? (string)TWILIO_CALLER_ID : '+19048349227', ENT_QUOTES)?>"
    action="<?=htmlspecialchars($base . '/voice/recording_callback.php?action=dial', ENT_QUOTES)?>"
        method="POST"
        recordingStatusCallback="<?=htmlspecialchars($callback, ENT_QUOTES)?>"
        recordingStatusCallbackMethod="POST"
        <?php if (defined('TWILIO_TRANSCRIBE_ENABLED') && TWILIO_TRANSCRIBE_ENABLED): ?>
        recordingTranscribe="true"
        recordingTranscribeCallback="<?=htmlspecialchars($callback, ENT_QUOTES)?>"
        <?php endif; ?>
        timeout="60">
    <Number><?=htmlspecialchars($to, ENT_QUOTES)?></Number>
  </Dial>
  <!-- Simple hangup if no answer - no voicemail prompts -->
  <Hangup />
</Response>

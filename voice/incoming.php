<?php
declare(strict_types=1);
header('Content-Type: text/xml');

// Load shared config (CRM/Twilio values live here)
$env = __DIR__ . '/../api/.env.local.php';
if (is_file($env)) {
  require $env;
}

$host = $_SERVER['HTTP_HOST'] ?? 'mechanicstaugustine.com';
$callback = 'https://' . $host . '/voice/recording_callback.php';
// Force personal phone for testing - config not loading properly
$to = '+19046634789';
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
        callerId="+19048349227"
    action="<?=htmlspecialchars('https://' . $host . '/voice/recording_callback.php?action=dial', ENT_QUOTES)?>"
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

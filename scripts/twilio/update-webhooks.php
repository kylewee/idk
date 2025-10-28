#!/usr/bin/env php
<?php
// Updates Twilio Incoming Phone Number VoiceUrl to the current ngrok URL.
// Requirements: TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and either TWILIO_NUMBER_SID or TWILIO_PHONE_NUMBER in api/.env.local.php
// Optional: set path via NGROK_API (default http://127.0.0.1:4040/api/tunnels)

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$env = $root . '/api/.env.local.php';
if (!is_file($env)) {
  fwrite(STDERR, "Missing env: $env\n" );
  exit(1);
}
require $env;

function getenv_default(string $k, string $d=''): string { $v = getenv($k); return $v === false ? $d : $v; }

$acct = defined('TWILIO_ACCOUNT_SID') ? (string)TWILIO_ACCOUNT_SID : '';
$auth = defined('TWILIO_AUTH_TOKEN') ? (string)TWILIO_AUTH_TOKEN : '';
$numSid = defined('TWILIO_NUMBER_SID') ? (string)TWILIO_NUMBER_SID : '';
$numE164 = defined('TWILIO_PHONE_NUMBER') ? (string)TWILIO_PHONE_NUMBER : '';

if ($acct === '' || $auth === '') {
  fwrite(STDERR, "Set TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN in api/.env.local.php\n");
  exit(2);
}

// Determine base public URL for webhooks
$public = '';
$override = getenv_default('VOICE_BASE_URL', '');
if ($override === '' && !empty($argv) && !empty($argv[1])) {
  $override = (string)$argv[1];
}
if ($override !== '') {
  $public = rtrim($override, '/');
} else {
  // Fallback: fetch from ngrok local API
  $api = getenv_default('NGROK_API', 'http://127.0.0.1:4040/api/tunnels');
  $json = @file_get_contents($api);
  if (!$json) {
    fwrite(STDERR, "ngrok API not reachable at $api. Is ngrok running? You can also pass base URL as first arg or VOICE_BASE_URL env.\n");
    exit(3);
  }
  $j = json_decode($json, true);
  if (!is_array($j) || empty($j['tunnels'])) {
    fwrite(STDERR, "No tunnels found at $api\n");
    exit(4);
  }
  foreach ($j['tunnels'] as $t) {
    if (!empty($t['public_url'])) { $public = (string)$t['public_url']; break; }
  }
  if ($public === '') {
    fwrite(STDERR, "No public_url in tunnels payload\n");
    exit(5);
  }
}

$voiceUrl = rtrim($public, '/') . '/voice/incoming.php';

// Resolve Phone Number SID if only E.164 provided
if ($numSid === '' && $numE164 !== '') {
  $url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($acct) . '/IncomingPhoneNumbers.json?PhoneNumber=' . rawurlencode($numE164);
  $ctx = stream_context_create(['http' => [
    'method' => 'GET',
    'header' => 'Authorization: Basic ' . base64_encode($acct . ':' . $auth),
    'timeout' => 15,
  ]]);
  $resp = @file_get_contents($url, false, $ctx);
  $list = json_decode((string)$resp, true);
  if (is_array($list) && !empty($list['incoming_phone_numbers'][0]['sid'])) {
    $numSid = (string)$list['incoming_phone_numbers'][0]['sid'];
  } else {
    fwrite(STDERR, "Unable to find number SID for $numE164. Set TWILIO_NUMBER_SID in env.\n");
    exit(6);
  }
}

if ($numSid === '') {
  fwrite(STDERR, "Set TWILIO_NUMBER_SID or TWILIO_PHONE_NUMBER in api/.env.local.php\n");
  exit(7);
}

// Update VoiceUrl
$url = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($acct) . '/IncomingPhoneNumbers/' . rawurlencode($numSid) . '.json';
$post = http_build_query([
  'VoiceUrl' => $voiceUrl,
  'VoiceMethod' => 'POST',
]);

$resp = '';
$http = 0;
$err = '';

if (function_exists('curl_init')) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_USERPWD => $acct . ':' . $auth,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 8,
  ]);
  $resp = (string)curl_exec($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errn = curl_errno($ch);
  $erre = curl_error($ch);
  curl_close($ch);
  if ($errn !== 0) { $err = $erre; }
} else {
  $headers = [
    'Authorization: Basic ' . base64_encode($acct . ':' . $auth),
    'Content-Type: application/x-www-form-urlencoded',
  ];
  $ctx = stream_context_create(['http' => [
    'method' => 'POST',
    'header' => implode("\r\n", $headers),
    'content' => $post,
    'timeout' => 20,
    'ignore_errors' => true,
  ]]);
  $resp = @file_get_contents($url, false, $ctx);
  $http = 0;
  if (isset($http_response_header) && is_array($http_response_header)) {
    foreach ($http_response_header as $line) {
      if (preg_match('~^HTTP/\S+\s+(\d{3})~', $line, $m)) { $http = (int)$m[1]; break; }
    }
  }
}

if ($http < 200 || $http >= 300) {
  fwrite(STDERR, "Failed to update VoiceUrl ($http): $err\n$resp\n");
  exit(8);
}

echo "Updated VoiceUrl to $voiceUrl for $numSid\n";

<?php
header('Content-Type: application/json');

// Minimal DB health check for Rukovoditel connection
$host = defined('DB_SERVER') ? DB_SERVER : (getenv('DB_HOST') ?: 'localhost');
$user = defined('DB_SERVER_USERNAME') ? DB_SERVER_USERNAME : (getenv('DB_USER') ?: 'root');
$pass = defined('DB_SERVER_PASSWORD') ? DB_SERVER_PASSWORD : (getenv('DB_PASS') ?: '');
$db   = defined('DB_DATABASE') ? DB_DATABASE : (getenv('DB_NAME') ?: '');
$port = defined('DB_SERVER_PORT') ? DB_SERVER_PORT : (getenv('DB_PORT') ?: '3306');

$start = microtime(true);
$mysqli = @new mysqli($host, $user, $pass, $db, (int)$port ?: null);
$ms = (int) ((microtime(true) - $start) * 1000);

if ($mysqli && $mysqli->connect_errno === 0) {
  echo json_encode(['ok' => true, 'host' => $host, 'db' => $db, 'latency_ms' => $ms]);
  $mysqli->close();
  exit;
}

http_response_code(500);
echo json_encode([
  'ok' => false,
  'error' => $mysqli ? $mysqli->connect_error : 'mysqli-init-failed',
  'host' => $host,
  'db' => $db,
]);

#!/usr/bin/env bash
set -euo pipefail

# Simple local dev bootstrap: starts PHP server and, if available, ngrok.
# Usage:
#   ./scripts/dev/start-local.sh
# Env vars:
#   PORT=8080 PHP_BIN=php NGROK_BIN=ngrok

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PORT="${PORT:-8080}"
PHP_BIN="${PHP_BIN:-php}"
NGROK_BIN="${NGROK_BIN:-ngrok}"

mkdir -p "$ROOT_DIR/.dev"
PIDFILE="$ROOT_DIR/.dev/pids"
: > "$PIDFILE"

echo "Starting PHP server on :$PORT (root=$ROOT_DIR)" >&2
"$PHP_BIN" -S 0.0.0.0:"$PORT" -t "$ROOT_DIR" >/dev/null 2>&1 &
PHP_PID=$!
echo "php:$PHP_PID" >> "$PIDFILE"

if command -v "$NGROK_BIN" >/dev/null 2>&1; then
  echo "Starting ngrok (http $PORT)" >&2
  "$NGROK_BIN" http "$PORT" >/dev/null 2>&1 &
  NGROK_PID=$!
  echo "ngrok:$NGROK_PID" >> "$PIDFILE"
  echo "Tip: open http://127.0.0.1:4040 to copy the public URL" >&2
  echo "Set Twilio webhooks to: <public_url>/voice/incoming.php and /voice/recording_callback.php" >&2
else
  echo "ngrok not found. Skipping tunnel. Install ngrok for Twilio webhooks." >&2
fi

echo "Started. To stop: ./scripts/dev/stop-local.sh" >&2

#!/usr/bin/env bash
set -euo pipefail

# Prints the first active ngrok public URL. Requires ngrok running locally.

API="http://127.0.0.1:4040/api/tunnels"
if ! command -v curl >/dev/null 2>&1; then
  echo "curl not found" >&2
  exit 1
fi

json=$(curl -sS "$API" || true)
if [[ -z "$json" ]]; then
  echo "ngrok API not reachable at $API (is ngrok running?)" >&2
  exit 2
fi

url=$(echo "$json" | grep -oE '"public_url":"https?://[^"]+' | head -n1 | cut -d '"' -f4)

if [[ -z "${url:-}" ]]; then
  echo "No public_url found. Open http://127.0.0.1:4040 to inspect." >&2
  exit 3
fi

echo "$url"

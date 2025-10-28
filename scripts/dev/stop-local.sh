#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PIDFILE="$ROOT_DIR/.dev/pids"

if [[ -f "$PIDFILE" ]]; then
  while IFS=: read -r name pid; do
    [[ -z "${pid:-}" ]] && continue
    if kill -0 "$pid" 2>/dev/null; then
      kill "$pid" >/dev/null 2>&1 || true
    fi
  done < "$PIDFILE"
  rm -f "$PIDFILE"
  echo "Stopped background processes."
else
  echo "Nothing to stop."
fi

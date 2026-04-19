#!/usr/bin/env bash
# Start PHP's built-in server (uses Homebrew PHP if php is not on PATH).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
PORT="${1:-8080}"
PHP_BIN="$(command -v php 2>/dev/null || true)"
if [[ -z "$PHP_BIN" && -x /opt/homebrew/bin/php ]]; then
  PHP_BIN="/opt/homebrew/bin/php"
fi
if [[ -z "$PHP_BIN" ]]; then
  echo "PHP not found. Install with: brew install php"
  exit 1
fi
if [[ ! -f "$ROOT/config.php" ]]; then
  echo "Missing config.php — run: cp config.sample.php config.php"
  exit 1
fi
echo "Serving $ROOT"
echo "Open: http://localhost:${PORT}/login.php"
echo "Press Ctrl+C to stop."
exec "$PHP_BIN" -S "localhost:${PORT}" -t "$ROOT"

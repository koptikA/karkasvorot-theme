#!/usr/bin/env bash
# Local stand: fresh WordPress (PHP 7.4) on http://127.0.0.1:${KV_PORT:-9400} with the theme mounted live.
# Every start rebuilds the site from scratch (in-memory), then seeds fixtures and activates the theme.
# Port: bash tools/serve.sh --port 9405   or   KV_PORT=9405 bash tools/serve.sh
set -euo pipefail
export MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL="*"  # Git Bash: keep /wordpress/... VFS paths as is
ROOT="$(cd "$(dirname "$0")/.." && (pwd -W 2>/dev/null || pwd))"
PORT="${KV_PORT:-9400}"
if [ "${1:-}" = "--port" ]; then PORT="$2"; shift 2; fi
# Secret for /kv-run.php (a cross-site form cannot send it as a header). Kept out of git.
TOKEN_FILE="$ROOT/tools/stand/.token"
[ -s "$TOKEN_FILE" ] || od -An -tx1 -N16 /dev/urandom | tr -d ' \n' > "$TOKEN_FILE"
exec npx -y @wp-playground/cli@latest server \
	--port "$PORT" --php 7.4 --login \
	--mount-dir "$ROOT/theme/karkasvorot" /wordpress/wp-content/themes/karkasvorot \
	--mount-dir "$ROOT/tools/fixtures" /kv-fixtures \
	--mount-dir "$ROOT/tools/stand" /kv-stand \
	--blueprint "$ROOT/tools/stand/blueprint.json" \
	"$@"

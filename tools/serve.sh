#!/usr/bin/env bash
# Local stand: fresh WordPress (PHP 7.4) on http://127.0.0.1:9400 with the theme mounted live.
# Every start rebuilds the site from scratch (in-memory), then seeds fixtures and activates the theme.
set -euo pipefail
export MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL="*"  # Git Bash: keep /wordpress/... VFS paths as is
ROOT="$(cd "$(dirname "$0")/.." && (pwd -W 2>/dev/null || pwd))"
exec npx -y @wp-playground/cli@latest server \
	--port 9400 --php 7.4 --login \
	--mount-dir "$ROOT/theme/karkasvorot" /wordpress/wp-content/themes/karkasvorot \
	--mount-dir "$ROOT/tools/fixtures" /kv-fixtures \
	--mount-dir "$ROOT/tools/stand" /kv-stand \
	--blueprint "$ROOT/tools/stand/blueprint.json" \
	"$@"

#!/usr/bin/env bash
# Plesk Git → "Extra implementatie-acties" / "Additional deployment actions"
# Plak in Plesk: bash scripts/plesk-git-hook.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

LOG_DIR="${ROOT}/storage/logs"
LOG_FILE="${LOG_DIR}/plesk-git-deploy.log"
mkdir -p "$LOG_DIR"

{
  echo "=== Plesk Git deploy $(date -Iseconds) ==="
  echo "User: $(whoami)  PWD: $(pwd)"
  bash "${ROOT}/scripts/plesk-deploy.sh"
  echo "=== Klaar $(date -Iseconds) ==="
} >>"$LOG_FILE" 2>&1

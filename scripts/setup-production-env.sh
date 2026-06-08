#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP_BIN="${PHP_BIN:-php}"
if [[ -x /opt/plesk/php/8.5/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.5/bin/php
elif [[ -x /opt/plesk/php/8.4/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.4/bin/php
elif [[ -x /opt/plesk/php/8.3/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.3/bin/php
fi

TEMPLATE=".env.production.greidefugels.nl"
TARGET=".env"

if [[ ! -f "$TEMPLATE" ]]; then
  echo "Niet gevonden: $TEMPLATE"
  exit 1
fi

read_existing() {
  local key="$1"
  if [[ ! -f "$TARGET" ]]; then
    return 0
  fi
  local line
  line="$(grep -E "^${key}=" "$TARGET" | head -1 || true)"
  line="${line#${key}=}"
  line="${line%\"}"
  line="${line#\"}"
  printf '%s' "$line"
}

EXISTING_KEY=""
EXISTING_DB_DATABASE=""
EXISTING_DB_USERNAME=""
EXISTING_DB_PASSWORD=""
EXISTING_GOOGLE_AI_API_KEY=""
EXISTING_OPENAI_API_KEY=""
EXISTING_AI_VISION_PROVIDER=""

if [[ -f "$TARGET" ]]; then
  EXISTING_KEY="$(read_existing APP_KEY)"
  EXISTING_DB_DATABASE="$(read_existing DB_DATABASE)"
  EXISTING_DB_USERNAME="$(read_existing DB_USERNAME)"
  EXISTING_DB_PASSWORD="$(read_existing DB_PASSWORD)"
  EXISTING_GOOGLE_AI_API_KEY="$(read_existing GOOGLE_AI_API_KEY)"
  EXISTING_OPENAI_API_KEY="$(read_existing OPENAI_API_KEY)"
  EXISTING_AI_VISION_PROVIDER="$(read_existing AI_VISION_PROVIDER)"
  cp "$TARGET" "${TARGET}.bak.$(date +%Y%m%d%H%M%S)"
fi

cp "$TEMPLATE" "$TARGET"

if [[ -n "$EXISTING_KEY" ]]; then
  sed -i "s|^APP_KEY=.*|APP_KEY=${EXISTING_KEY}|" "$TARGET"
else
  "$PHP_BIN" artisan key:generate --force
fi

if [[ $# -ge 3 ]]; then
  "$PHP_BIN" scripts/set-env-db.php "$1" "$2" "$3"
  echo "MySQL-gegevens ingevuld."
elif [[ -n "$EXISTING_DB_PASSWORD" && -n "$EXISTING_DB_DATABASE" && -n "$EXISTING_DB_USERNAME" ]]; then
  "$PHP_BIN" scripts/set-env-db.php "$EXISTING_DB_DATABASE" "$EXISTING_DB_USERNAME" "$EXISTING_DB_PASSWORD"
fi

if [[ -n "$EXISTING_GOOGLE_AI_API_KEY" ]]; then
  export GOOGLE_AI_API_KEY="$EXISTING_GOOGLE_AI_API_KEY"
fi
if [[ -n "$EXISTING_OPENAI_API_KEY" ]]; then
  export OPENAI_API_KEY="$EXISTING_OPENAI_API_KEY"
fi
if [[ -n "$EXISTING_AI_VISION_PROVIDER" ]]; then
  export AI_VISION_PROVIDER="$EXISTING_AI_VISION_PROVIDER"
fi
if [[ -n "$EXISTING_GOOGLE_AI_API_KEY" || -n "$EXISTING_OPENAI_API_KEY" ]]; then
  "$PHP_BIN" scripts/set-env-ai.php
fi

chmod 640 "$TARGET" 2>/dev/null || true

echo "Klaar."

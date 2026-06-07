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
  if [[ -f "$TARGET" ]]; then
    grep -E "^${key}=" "$TARGET" | head -1 | cut -d= -f2- || true
  fi
}

EXISTING_KEY=""
EXISTING_DB_CONNECTION=""
EXISTING_DB_HOST=""
EXISTING_DB_PORT=""
EXISTING_DB_DATABASE=""
EXISTING_DB_USERNAME=""
EXISTING_DB_PASSWORD=""

if [[ -f "$TARGET" ]]; then
  EXISTING_KEY="$(read_existing APP_KEY)"
  EXISTING_DB_CONNECTION="$(read_existing DB_CONNECTION)"
  EXISTING_DB_HOST="$(read_existing DB_HOST)"
  EXISTING_DB_PORT="$(read_existing DB_PORT)"
  EXISTING_DB_DATABASE="$(read_existing DB_DATABASE)"
  EXISTING_DB_USERNAME="$(read_existing DB_USERNAME)"
  EXISTING_DB_PASSWORD="$(read_existing DB_PASSWORD)"
  cp "$TARGET" "${TARGET}.bak.$(date +%Y%m%d%H%M%S)"
fi

cp "$TEMPLATE" "$TARGET"

if [[ -n "$EXISTING_KEY" ]]; then
  sed -i "s|^APP_KEY=.*|APP_KEY=${EXISTING_KEY}|" "$TARGET"
else
  "$PHP_BIN" artisan key:generate --force
fi

if [[ -n "$EXISTING_DB_CONNECTION" ]]; then
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=${EXISTING_DB_CONNECTION}|" "$TARGET"
fi
if [[ -n "$EXISTING_DB_HOST" ]]; then
  sed -i "s|^DB_HOST=.*|DB_HOST=${EXISTING_DB_HOST}|" "$TARGET"
fi
if [[ -n "$EXISTING_DB_PORT" ]]; then
  sed -i "s|^DB_PORT=.*|DB_PORT=${EXISTING_DB_PORT}|" "$TARGET"
fi
if [[ -n "$EXISTING_DB_DATABASE" ]]; then
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${EXISTING_DB_DATABASE}|" "$TARGET"
fi
if [[ -n "$EXISTING_DB_USERNAME" ]]; then
  sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${EXISTING_DB_USERNAME}|" "$TARGET"
fi
if [[ -n "$EXISTING_DB_PASSWORD" ]]; then
  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${EXISTING_DB_PASSWORD}|" "$TARGET"
fi

if [[ $# -ge 3 ]]; then
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" "$TARGET"
  sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" "$TARGET"
  sed -i "s|^DB_PORT=.*|DB_PORT=3306|" "$TARGET"
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$1|" "$TARGET"
  sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$2|" "$TARGET"
  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$3|" "$TARGET"
  echo "MySQL-gegevens ingevuld."
fi

chmod 640 "$TARGET" 2>/dev/null || true

echo "Klaar. Daarna:"
echo "  php artisan config:clear && php artisan config:cache"
echo "  chmod -R ug+rwx storage bootstrap/cache"

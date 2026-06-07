#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

TEMPLATE=".env.production.greidefugels.nl"
TARGET=".env"

if [[ ! -f "$TEMPLATE" ]]; then
  echo "Niet gevonden: $TEMPLATE"
  exit 1
fi

EXISTING_KEY=""
if [[ -f "$TARGET" ]]; then
  EXISTING_KEY="$(grep -E '^APP_KEY=' "$TARGET" | head -1 | cut -d= -f2- || true)"
  cp "$TARGET" "${TARGET}.bak.$(date +%Y%m%d%H%M%S)"
fi

cp "$TEMPLATE" "$TARGET"

if [[ -n "$EXISTING_KEY" && "$EXISTING_KEY" != "" ]]; then
  sed -i "s|^APP_KEY=.*|APP_KEY=${EXISTING_KEY}|" "$TARGET"
else
  php artisan key:generate --force
fi

if [[ $# -ge 3 ]]; then
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" "$TARGET"
  sed -i "s|^DB_HOST=.*|DB_HOST=localhost|" "$TARGET"
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

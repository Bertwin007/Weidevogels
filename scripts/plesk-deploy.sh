#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f composer.json ]]; then
  echo "composer.json niet gevonden in ${ROOT}."
  echo "Plesk Git-deploy moet naar httpdocs wijzen, niet naar httpdocs/public."
  exit 1
fi

PHP_BIN="${PHP_BIN:-php}"
if [[ -x /opt/plesk/php/8.5/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.5/bin/php
elif [[ -x /opt/plesk/php/8.4/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.4/bin/php
elif [[ -x /opt/plesk/php/8.3/bin/php ]]; then
  PHP_BIN=/opt/plesk/php/8.3/bin/php
fi

export PHP_BIN

if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
  composer dump-autoload --optimize --no-interaction
elif [[ -f /usr/local/psa/var/modules/composer/composer.phar ]]; then
  "$PHP_BIN" /usr/local/psa/var/modules/composer/composer.phar install --no-dev --optimize-autoloader --no-interaction
  "$PHP_BIN" /usr/local/psa/var/modules/composer/composer.phar dump-autoload --optimize --no-interaction
else
  echo "Composer niet gevonden. Installeer Composer in Plesk (PHP Composer) of via SSH."
  exit 1
fi

if [[ -f "$ROOT/.env.db.local" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "$ROOT/.env.db.local"
  set +a
  bash scripts/setup-production-env.sh "${DB_DATABASE:-greidefugels}" "${DB_USERNAME:-greidefugels}" "${DB_PASSWORD:?DB_PASSWORD ontbreekt in .env.db.local}"
else
  bash scripts/setup-production-env.sh
fi

if [[ -f "$ROOT/.env.ai.local" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "$ROOT/.env.ai.local"
  set +a
  "$PHP_BIN" scripts/set-env-ai.php
fi

"$PHP_BIN" artisan optimize:clear --no-interaction 2>/dev/null || "$PHP_BIN" artisan config:clear

if ! "$PHP_BIN" artisan migrate:status --no-interaction >/dev/null 2>&1; then
  echo ""
  echo "MySQL niet bereikbaar. Eenmalig op de server:"
  echo "  cp .env.db.local.example .env.db.local"
  echo "  nano .env.db.local"
  echo "  bash scripts/plesk-deploy.sh"
  exit 1
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan db:seed --force

mkdir -p storage/app/public/observations
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs

STORAGE_LINK="$(pwd)/public/storage"
STORAGE_TARGET="$(pwd)/storage/app/public"

if [[ -e "$STORAGE_LINK" && ! -L "$STORAGE_LINK" ]]; then
  rm -rf "$STORAGE_LINK"
fi

ln -sfn "$STORAGE_TARGET" "$STORAGE_LINK"

find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} + 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

echo "Deploy klaar: ${ROOT}"
echo "Test: https://greidefugels.nl/gezondheid"

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
elif [[ -f /usr/local/psa/var/modules/composer/composer.phar ]]; then
  "$PHP_BIN" /usr/local/psa/var/modules/composer/composer.phar install --no-dev --optimize-autoloader --no-interaction
else
  echo "Composer niet gevonden. Installeer Composer in Plesk (PHP Composer) of via SSH."
  exit 1
fi

bash scripts/setup-production-env.sh

if [[ ! -f database/database.sqlite ]]; then
  mkdir -p database
  touch database/database.sqlite
fi

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan db:seed --force
"$PHP_BIN" artisan storage:link --force 2>/dev/null || true

"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan config:cache

chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "Deploy klaar: ${ROOT}"

APP_NAME="Greidefugels"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://greidefugels.nl

APP_LOCALE=nl
APP_FALLBACK_LOCALE=nl
APP_FAKER_LOCALE=nl_NL

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

# Start zonder database — later MySQL invullen
DB_CONNECTION=sqlite
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log

ANF_DONATION_URL=https://www.agrarischnatuurfondsfryslan.nl/steun?utm_source=greidefugels&utm_medium=moment&utm_campaign=weidevogels

VITE_APP_NAME="${APP_NAME}"

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

# Productie: MySQL via Plesk (geen SQLite — rechtenproblemen op shared hosting)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=greidefugels
DB_USERNAME=greidefugels
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log

ANF_DONATION_URL=https://agrarischnatuurfondsfryslan.nl/donateur-worden/?utm_source=greidefugels&utm_medium=moment&utm_campaign=weidevogels

VITE_APP_NAME="${APP_NAME}"

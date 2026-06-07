# Testpagina op greidefugels.nl

## Wat je ziet

Homepage toont: **greidefugels.nl werkt** (groene testpagina, geen database nodig).

## Plesk

1. **Git** → `https://github.com/Bertwin007/Weidevogels.git` → branch `main` → **Deploy now**
2. **Document root** → `httpdocs/public`
3. SSH als user **greidefugels**:

```bash
cd ~/httpdocs
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate --force
```

In `.env` minimaal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://greidefugels.nl
```

```bash
php artisan config:cache
chmod -R ug+rwx storage bootstrap/cache
```

4. **PHP opnieuw starten** in Plesk → site openen: https://greidefugels.nl

## Ultra-snelle test (zonder Laravel)

Als Apache 500 blijft, tijdelijk in SSH:

```bash
echo 'OK greidefugels.nl' > ~/httpdocs/public/ping.txt
```

Open https://greidefugels.nl/ping.txt — werkt dat wel, dan is Laravel/config het probleem.

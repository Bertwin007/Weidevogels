# Testpagina op greidefugels.nl

## Wat je ziet

- **https://greidefugels.nl** → testpagina (Laravel of statische fallback)
- **https://greidefugels.nl/test.html** → altijd statisch, geen Laravel nodig

## Plesk (belangrijk)

1. **Document root** = `httpdocs/public` (niet alleen `httpdocs`)
2. **Git** → Deploy now (branch `main`)

## Minimale deploy (statisch testen)

SSH als **greidefugels**:

```bash
cd ~/httpdocs
git pull origin main
```

Open direct: **https://greidefugels.nl/test.html**

Werkt dat? → server OK. Daarna Laravel afmaken:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate --force
# .env: APP_URL=https://greidefugels.nl, APP_DEBUG=false
php artisan config:cache
chmod -R ug+rwx storage bootstrap/cache
```

## Werkt test.html ook niet?

→ Document root of domein wijst niet naar `httpdocs/public`. In Plesk **Hostinginstellingen** controleren.

## Ultra-check

```bash
echo OK > ~/httpdocs/public/ping.txt
```

Open **https://greidefugels.nl/ping.txt**

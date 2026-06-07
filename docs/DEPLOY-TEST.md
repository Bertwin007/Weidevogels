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

Meestal **kapotte `.htaccess`** (Apache 500 op álle bestanden) of verkeerde document root.

### Snel fix op server (SSH)

```bash
cd ~/httpdocs/public
mv .htaccess .htaccess.bak
echo OK > ping.txt
```

Open **https://greidefugels.nl/ping.txt** — werkt dat? → `.htaccess` was de oorzaak. Daarna:

```bash
cd ~/httpdocs
git pull origin main
```

(de repo heeft een Plesk-veilige `.htaccess` zonder `Options -Indexes`)

### Document root

Moet **`httpdocs/public`** zijn (niet alleen `httpdocs`).

### Apache error log

Plesk → greidefugels.nl → **Logs** → Apache error — zoek regels met `.htaccess` of `Options`.

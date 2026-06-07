# Laravel productie — greidefugels.nl

## Plesk

- **Git deploy-pad:** `httpdocs` (hele repo, **niet** `httpdocs/public`)
- **Document root:** `httpdocs/public`
- Git: `Weidevogels` branch `main`
- SSL: Let's Encrypt (geen wildcard)
- Redirect: Hostinginstellingen of Apache & nginx → HTTP naar HTTPS
- **Acties na deployment:** `bash httpdocs/scripts/plesk-deploy.sh`

Plesk roept `artisan` soms aan vanuit `public/`. Daarvoor staat `public/artisan` in de repo — die verwijst naar de echte `artisan` en `vendor/` in `httpdocs`.

### Fout: `public/vendor/autoload.php` niet gevonden

`composer install` is nog niet (goed) gedraaid. Oplossing:

```bash
cd ~/httpdocs
git pull origin main
bash scripts/plesk-deploy.sh
```

## SSH (user greidefugels)

```bash
cd ~/httpdocs
git pull origin main
bash scripts/plesk-deploy.sh
# Of met MySQL: na setup-production-env handmatig DB-gegevens, of pas het script aan
```

Handmatig (zelfde als het script):

```bash
composer install --no-dev --optimize-autoloader
bash scripts/setup-production-env.sh
php artisan config:clear && php artisan view:clear && php artisan config:cache
chmod -R ug+rwx storage bootstrap/cache
```

## Test

- https://greidefugels.nl (Laravel testpagina)
- https://greidefugels.nl/test.html (statische fallback)

## Veelvoorkomend

- Apache 500 → `.htaccess` zonder `Options -Indexes` (staat goed in repo)
- Git conflict → `git fetch origin && git reset --hard origin/main`

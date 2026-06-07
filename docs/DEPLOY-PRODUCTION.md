# Laravel productie — greidefugels.nl

## Plesk

- Document root: `httpdocs/public`
- Git: `Weidevogels` branch `main`
- SSL: Let's Encrypt (geen wildcard)
- Redirect: Hostinginstellingen of Apache & nginx → HTTP naar HTTPS

## SSH (user greidefugels)

```bash
cd ~/httpdocs
git pull origin main
composer install --no-dev --optimize-autoloader
bash scripts/setup-production-env.sh
# Of met MySQL: bash scripts/setup-production-env.sh DB_NAAM DB_USER 'WACHTWOORD'
php artisan config:clear
php artisan config:cache
chmod -R ug+rwx storage bootstrap/cache
```

## Test

- https://greidefugels.nl (Laravel testpagina)
- https://greidefugels.nl/test.html (statische fallback)

## Veelvoorkomend

- Apache 500 → `.htaccess` zonder `Options -Indexes` (staat goed in repo)
- Git conflict → `git fetch origin && git reset --hard origin/main`

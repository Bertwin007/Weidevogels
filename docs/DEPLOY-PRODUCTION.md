# Laravel productie — greidefugels.nl

## Plesk

- **Git deploy-pad:** `httpdocs` (hele repo, **niet** `httpdocs/public`)
- **Document root:** `httpdocs/public`
- **Database:** MySQL via Plesk (geen SQLite op productie)
- **Acties na deployment:** `bash httpdocs/scripts/plesk-deploy.sh`

## Eerste keer: MySQL koppelen

1. Plesk → **Databases** → database `greidefugels` + user `greidefugels` (of bestaande gebruiken)
2. SSH:

```bash
cd ~/httpdocs
git pull origin main
cp .env.db.local.example .env.db.local
nano .env.db.local
# DB_PASSWORD = wachtwoord uit Plesk
bash scripts/plesk-deploy.sh
```

Alternatief zonder bestand:

```bash
bash scripts/setup-production-env.sh greidefugels greidefugels 'PLESK_WACHTWOORD'
bash scripts/plesk-deploy.sh
```

`.env.db.local` staat in `.gitignore` — wachtwoord komt nooit in GitHub.

## Volgende deploys

```bash
cd ~/httpdocs
git pull origin main
bash scripts/plesk-deploy.sh
```

MySQL-gegevens blijven bewaard in `.env` (script overschrijft ze niet meer).

## Veelvoorkomend

| Fout | Oplossing |
|---|---|
| `unable to open database file` | Oude SQLite-.env → bovenstaande MySQL-stappen |
| `public/vendor/autoload.php` | `bash scripts/plesk-deploy.sh` (composer install) |
| Git conflict | `git fetch origin && git reset --hard origin/main` |

## Test

- https://greidefugels.nl
- https://greidefugels.nl/upload

# Laravel productie — greidefugels.nl

## Plesk

- **Git deploy-pad:** `httpdocs` (hele repo, **niet** `httpdocs/public`)
- **Document root:** `httpdocs/public`
- **Database:** MySQL via Plesk (geen SQLite op productie)

### Git + automatische deploy (aanbevolen)

In Plesk: **Websites & domeinen** → **greidefugels.nl** → **Git** → repository-instellingen.

1. **Implementatiepad (deployment path):** `httpdocs`
2. Vink aan: **Extra implementatie-acties inschakelen** (*Enable additional deployment actions*)
3. Plak dit in het tekstveld (werkt vanuit `httpdocs`):

```bash
bash scripts/plesk-git-hook.sh
```

Of korter, zonder logbestand:

```bash
bash scripts/plesk-deploy.sh
```

4. Sla op. Bij **Nu implementeren** / **Deploy now** doet Plesk dan: `git pull` + composer + migrate + cache.

Log na automatische deploy (alleen bij de hook-variant):

```bash
tail -50 ~/httpdocs/storage/logs/plesk-git-deploy.log
```

**Let op:** pad `httpdocs/scripts/...` alleen gebruiken als je het commando vanuit je home-directory (`~`) start, niet in het Plesk-tekstveld.

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

## ESG-rapporten (admin)

Na deploy: **Beheer → ESG-rapporten**. PDF gebruikt `barryvdh/laravel-dompdf` (via `composer install` in `plesk-deploy.sh`).

Partner-metadata (pakket, m², habitat, e-mail): `config/esg.php` → `partners['bedrijfs-slug']`.

Versturen naar partner: stel `MAIL_MAILER=smtp` in `.env` (standaard is `log` — dan verschijnt de mail in `storage/logs/laravel.log`). Verzendlog: `storage/logs/esg-report-deliveries.log`.

## Test

- https://greidefugels.nl
- https://greidefugels.nl/gezondheid
- https://greidefugels.nl/admin/esg-rapporten (ingelogd als admin)

# SSL & HTTPS op greidefugels.nl (Plesk)

## Belangrijk: geen wildcard, geen Strato TXT

Gebruik **geen** wildcard-certificaat. Strato `_acme-challenge` is niet nodig.

---

## Stap 1 — Oude aanvraag annuleren

Staat Plesk nog op DNS / `_acme-challenge`? → **Cancel**.

---

## Stap 2 — Let’s Encrypt (normaal)

**Websites & domeinen** → **greidefugels.nl** → **SSL/TLS-certificaten**

1. Klik **Certificaat installeren** / **Installeren** / **Let’s Encrypt**
2. Vinkjes:
   - ✅ **greidefugels.nl**
   - ✅ **www.greidefugels.nl** (mag)
   - ❌ **Wildcard** / `*.greidefugels.nl` — **UIT**
   - ❌ **Beveilig wildcard-domeinen** — **UIT**
3. E-mail invullen → **Installeren**

Status moet **Geldig** worden (groen).

Werkt dit niet? Noteer de **exacte fouttekst** uit Plesk.

---

## Stap 3 — Redirect-vinkje (Hostinginstellingen)

Het redirect-vinkje staat **niet** op de pagina SSL/TLS-certificaten.

**Websites & domeinen** → **greidefugels.nl** → **Hosting & DNS** → **Hostinginstellingen**

Onder **Beveiliging**:

1. Eerst ✅ **SSL/TLS-ondersteuning**
2. Kies bij **Certificaat** je Let’s Encrypt-certificaat
3. Dan pas ✅ **Permanent SEO-safe 301 redirect from HTTP to HTTPS**
4. **OK**

**Grijs / niet klikbaar?** → Eerst stap 2 afmaken (geldig certificaat).

---

## Alternatief redirect-vinkje

**Websites & domeinen** → **greidefugels.nl** → **Apache & nginx-instellingen**

Zoek: **Permanent SEO-safe 301 redirect from HTTP to HTTPS** → aan → **OK**

---

## Redirect werkt niet via `.htaccess`

Op Plesk regelt **nginx** SSL (niet Apache). Redirect moet in **Plesk**, niet in `.htaccess`.

### Optie A — Hostinginstellingen

**Hosting & DNS** → **Hostinginstellingen** → **Beveiliging**:

- ✅ SSL/TLS-ondersteuning
- Certificaat: **Lets Encrypt greidefugels.nl**
- ✅ Permanent SEO-safe 301 redirect from HTTP to HTTPS
- **OK**

### Optie B — Apache & nginx-instellingen

**Hosting & DNS** → **Apache & nginx-instellingen**:

- ✅ **Redirect from HTTP to HTTPS** (of SEO-safe 301)
- **OK**

Daarna **webservices herstarten** (knop op die pagina) of Plesk → **Services management** → nginx/apache restart.

### Test

```powershell
curl -I http://greidefugels.nl
```

Moet bevatten: `301` en `Location: https://greidefugels.nl/`

Browser: **https://greidefugels.nl/test.html** (met https, niet http).

---

## Test

1. **https://greidefugels.nl/test.html** → slotje, geen “Niet beveiligd”
2. **http://greidefugels.nl** → springt naar **https://**

---

## Strato

Alleen **A-record** naar de server. Geen TXT voor SSL.

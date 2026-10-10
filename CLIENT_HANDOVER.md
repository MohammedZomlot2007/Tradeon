# Tradeon — Client Handover (v1.0.0)

B2B business social network: companies publish posts, sourcing requests and RFQs,
discover partners via Smart Match, chat over business messenger, and monetize
through Paddle subscriptions.

Stack: Laravel 13 · PHP 8.3+ · React 18 · Inertia.js 2 · Vite 8 · Tailwind CSS 3 ·
MySQL (prod) · Redis (prod) · Reverb · Paddle · Pest.

## 1. Run locally (Windows PowerShell)

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed        # billing plans (FREE 30MB + PRO unlimited) + optional admin
php artisan storage:link
npm install --legacy-peer-deps
composer run dev           # serve + vite + queue + reverb together
```

Open: http://127.0.0.1:8000

Create an administrator:

```powershell
php artisan tradeon:admin --name="Full Name" --username="handle" --email="admin@example.com"
```

Required PHP extensions: `mbstring`, `gd`, `sqlite3/pdo_sqlite` (local), plus
`redis` and `pdo_mysql` on production.

## 2. Verified state

- Pest suite: **114 tests, 340 assertions — all passing** (`php artisan test`)
- Frontend production build: passing (`npm run build`)
- No secrets in this repo: `.env`, `*.sqlite`, logs, uploads and `vendor/` /
  `node_modules/` are git-ignored. Copy `.env.example` → `.env` and fill values.

## 3. Key business rules delivered

- **Free plan (30 MB):** every new account automatically gets an active FREE
  subscription with 30 MB of publishing storage (`FREE_STORAGE_MB` in `.env`).
- **Over-quota → payment page:** publishing anything (post, image, verification
  document, logo/cover, showcase, factory, product, request) while over quota
  redirects to `/pricing` automatically (API calls receive `402 + upgrade_url`).
- **PRO plan:** unlimited publishing storage, paid via Paddle (sandbox keys must
  be replaced with live keys; webhooks verified server-side).
- **Storage usage** is visible to each company on `/billing`.

## 4. Going live (production)

Full runbook: `deploy/production-runbook.md` (+ `deploy/nginx.tradeon.conf`,
`deploy/supervisor.tradeon.conf`). Summary:

1. Server: PHP 8.3+ (mbstring, gd, redis, pdo_mysql), MySQL, Redis, Node 20+.
2. Copy `.env.production.example` → `.env.production`, fill: `APP_KEY`, MySQL,
   Redis, SMTP, Reverb, Paddle (live keys + price IDs + webhook secret),
   `REVERB_ALLOWED_ORIGINS`, admin bootstrap vars.
3. `composer install --no-dev --optimize-autoloader`
4. `npm ci && npm run build`
5. `php artisan migrate --force && php artisan db:seed --force`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Queue + Reverb under supervisor, scheduler every minute, daily
   `php artisan tradeon:backup` (02:30 cron), S3-compatible disk for files.

Docs: `README.md` (setup + troubleshooting) · `BILLING_SETUP.md` (Paddle) ·
`FINAL_AUDIT_REPORT.md` (last audit).

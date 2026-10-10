# Tradeon — Global Business Social Network

Tradeon is a production B2B business social network connecting manufacturers, suppliers,
distributors, buyers, brands and business professionals. Companies publish business posts,
sourcing requests and RFQs, discover partners through deterministic Smart Match, chat over
a business messenger (Laravel Reverb + polling fallback), and monetize through Paddle
subscription billing.

Stack: Laravel 13 · PHP 8.3+ · React 18 · Inertia.js 2 · Vite 8 · Tailwind CSS 3 ·
MySQL (prod) · Redis (prod) · Reverb · Paddle · Pest.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed        # billing plans + env-based admin bootstrap (no demo data)
npm install --legacy-peer-deps
npm run build              # or: npm run dev (see composer run dev)
php artisan serve
```

> `npm install` needs `--legacy-peer-deps` because `@vitejs/plugin-react@4` declares a
> Vite ≤7 peer while the project builds with Vite 8 (verified working).

Create an administrator (never hardcoded):

```bash
php artisan tradeon:admin --name="Full Name" --username="handle" --email="admin@example.com"
# or non-interactively:
php artisan tradeon:admin --name="..." --username="..." --email="..." --password="..." --force
```

Alternatively set `TRADEON_ADMIN_NAME/USERNAME/EMAIL/PASSWORD` in `.env` before `db:seed`.

Demo content is opt-in and never seeded by default:

```bash
php artisan db:seed --class=BizLinkSeeder   # local development only
```

## Environment

All variables are documented in `.env.example` (local) and `.env.production.example`
(production). Production requires: `APP_KEY`, MySQL, Redis, SMTP, Reverb credentials,
Paddle client token + API key + price IDs + webhook secret, and `REVERB_ALLOWED_ORIGINS`.

## Key flows

| Area | Entry points |
|---|---|
| Feed (all / following / companies / RFQ) | `GET /feed`, `POST /posts`, like/comment/repost/save/report |
| Company onboarding | `GET/POST /onboarding/company` (one company per user, slug collision-safe) |
| Verification | `GET/POST /verification` (private-disk uploads), `GET /verification/{id}/document` (owner/admin only) |
| Factory & product showcases | `POST/PUT/DELETE /factory`, `/showcases` (company-owner only, managed on the company page) |
| Business requests & RFQ quotes | `GET/POST /requests`, `POST /rfq/{id}/quote` (active-only, one quote per company, no self-quotes) |
| Messenger | `GET /messages`, chat requests + accept/decline, realtime via Reverb private channels, 4s polling fallback |
| Notifications | `GET /notifications` (page) + JSON polling endpoint, read / read-all |
| Global search | `GET /search` (companies, manufacturers, products, requests) |
| Smart Match | `GET /smart-match`, `POST /api/smart-match` (deterministic weighted scoring, no randomness) |
| Billing | `GET /pricing`, `POST /billing/checkout/{plan}`, `GET /billing`, `POST /billing/webhooks/paddle` (signature-verified, idempotent) |
| Admin | `GET /admin` (users with search/filters, verification queue with notes + documents, report moderation, audit log) |

Paid access is granted exclusively by verified server-side webhook state — never by
browser checkout callbacks.

## Storage strategy

| Content | Disk | Visibility |
|---|---|---|
| Verification documents | `local` (`storage/app/private`) | private; served only through the authorized download route |
| Company logos/covers, public media | `public` (`storage/app/public`) | public via `storage` symlink |
| Production files | S3-compatible object storage | configure `FILESYSTEM_DISK` per environment |

## Realtime

```bash
php artisan reverb:start   # development
```

Configure `REVERB_*` plus `VITE_REVERB_*` for the browser client. In production run
Reverb behind TLS/reverse proxy and set `REVERB_ALLOWED_ORIGINS` to the real domain(s).
Private channels (`conversations.{id}`) authorize participants server-side
(`routes/channels.php`).

## Queue & scheduler

```bash
php artisan queue:work --tries=3
php artisan schedule:work   # or cron: php artisan schedule:run every minute
```

## Testing

```bash
php artisan test            # full Pest suite (auth, feed, RFQ, messenger, admin, billing, i18n flows)
```

Feature tests call `withoutVite()` so they never depend on frontend build artifacts.

## Deployment checklist

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --force        # plans only (+ admin only if TRADEON_* env set)
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:work --tries=3   # under a process supervisor
```

Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_FORCE_HTTPS=true`,
`SESSION_SECURE_COOKIE=true` with a real `APP_KEY`. Use encrypted daily database
backups and a monitored log sink. Never commit `.env`, `database/*.sqlite`,
`storage/logs/*`, `vendor/` or `node_modules/`.

## Troubleshooting

| Symptom | Fix |
|---|---|
| `Reverb ServerProviderManager::getDefaultDriver` type error | `config/reverb.php` must match the installed `laravel/reverb` version (regenerate from `vendor/laravel/reverb/config/reverb.php`) |
| `Please provide a valid cache path` | ensure `config/view.php` exists and `storage/framework/{views,cache,sessions}` exist; run `php artisan optimize:clear` |
| `Class "Redis" not found` locally | local `.env` must use `database`/`array` stores, not `redis` (no extension installed) |
| `Unknown adapter`/Vite manifest errors in tests | tests use `withoutVite()`; for real pages run `npm run build` |
| Paddle checkout shows "Setup Required" | fill `PADDLE_CLIENT_TOKEN` + plan price IDs; webhook destination `POST /billing/webhooks/paddle` |

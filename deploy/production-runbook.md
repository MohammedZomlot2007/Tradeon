# Tradeon Production Runbook

1. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`, and use a real APP_KEY.
2. Use MySQL, Redis, SMTP, object storage, and a queue worker. Do not use the development `log` mailer or database cache in production.
3. Run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`.
4. Run `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
5. Run `php artisan queue:work --tries=3` under a process supervisor. Run `php artisan schedule:work` or a cron entry calling `php artisan schedule:run` every minute. See `deploy/supervisor.tradeon.conf`.
6. Configure Reverb with `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and run `php artisan reverb:start` behind TLS/reverse proxy.
7. Configure Paddle production Client Token, API key, Price IDs and webhook secret. Keep secrets outside source control.
8. Use daily encrypted database backups (`php artisan tradeon:backup`, scheduled daily at 02:30) and test restoration regularly. Backups live in `storage/app/backups`.
9. Send application and queue errors to a monitored log sink. Never expose stack traces or credentials to users.
10. Smoke check after every deploy: `php artisan tradeon:smoke` (DB, routes, build manifest, storage link). Wire it to your uptime monitor.
11. Optional Sentry: `composer require sentry/sentry-laravel`, set `SENTRY_LARAVEL_DSN`, then `php artisan sentry:publish --dsn=...`. Leave empty to keep file/STDERR logging only.
12. Uptime: monitor `GET /up` (Laravel health) and `GET /sitemap.xml` every minute from an external checker.

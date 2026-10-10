# Tradeon Final Hardening Changelog

Implemented on v6.1-Billing:

- Production security middleware with security headers and HSTS in HTTPS/production.
- Suspended-account enforcement and audit log storage.
- Rate limiting added to sensitive business/social/billing actions.
- Notification center with database notifications for connections, contact requests, messages, likes and comments, and RFQ quotations.
- Laravel Reverb/Pusher client scaffolding plus private conversation channels and a 4-second polling fallback for Messenger.
- Global search endpoint/page for companies, manufacturers, products and business requests with filters and pagination.
- Smart Match expanded with weighted scoring for business type, industry, products/services, markets, MOQ, certifications, production capacity, verification and response rate; returns reasons.
- English/Arabic locale foundation with persistent session locale and translation JSON files.
- Admin user suspension/restore, report status workflow and audit log visibility.
- File upload safety validation rule for future upload endpoints.
- Production `.env` template and Nginx/deployment runbook.
- Removed production dependence on demo data; the development seeder remains opt-in.

Verification:
- PHP syntax lint: 118 PHP files passed.
- Full Laravel test suite and Vite production build still require installing project dependencies in a full PHP/Node environment.
- `composer.lock` must be refreshed after installing/updating the new `laravel/reverb` dependency.

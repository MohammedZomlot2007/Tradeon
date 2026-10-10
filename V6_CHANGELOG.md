# Tradeon v6 — Hardening Changelog

## Completed
- Added `users.role` with `user` default and secure admin middleware.
- Restricted admin dashboard and verification actions to admins only.
- Removed automatic demo/admin account creation from the default seeder.
- Added optional first-admin bootstrap through `TRADEON_ADMIN_*` environment variables.
- Registered full authenticated Product resource routes.
- Added defense-in-depth authorization to Product create/index endpoints.
- Hardened Messenger conversation access so only participants can read/send messages.
- Hardened chat request responses so only the receiver can accept/decline.
- Prevented duplicate active chat relationships and self chat requests.
- Reworked Smart Match from random scores to deterministic weighted matching.
- Added Smart Match request validation, keyword matching, verification and response-rate signals.
- Added rate limiting/authentication to Smart Match API.
- Added feature tests for admin authorization, messenger authorization, product routes and Smart Match scoring.
- Removed `.env` from the deliverable package so local secrets are not shipped.

## Validation
- PHP syntax sweep: passed across `app`, `bootstrap`, `config`, `database`, `routes`, and `tests`.
- Composer/Vite full runtime test: not executed in this container because Composer is unavailable and npm dependency installation could not complete within the environment.

## v6.1 Logic & Copy Fixes — 2026-09-16

- Replaced random dashboard profile views with persisted company profile view counts.
- Added `profile_views` migration and company model attribute.
- Dashboard profile completeness is now calculated from actual company fields instead of a fixed percentage.
- Fixed broken `/company/create` link to the real `/onboarding/company` route.
- Fixed Messenger contact links to open a real B2B contact-request flow using `recipient` and `purpose` query parameters.
- Fixed Messenger participant/recipient resolution when the authenticated user is the second conversation participant.
- Removed fake `[Translated]` behavior from post cards instead of presenting unchanged text as translated.
- Enabled the Manufacturer Directory `moq` query filter and aligned its copy with the actual query behavior.
- Updated authentication feature tests to match the required registration/onboarding flow.
- Added feature tests for persisted company profile views and the Messenger contact-request entry flow.
- Corrected several user-facing labels and capitalization issues.

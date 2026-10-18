# CI application verification and screenshots

The workflow [Application verification](../.github/workflows/application-verification.yml) creates an isolated local `.env` from `.env.example`, generates a temporary Laravel key, uses a disposable SQLite database, installs dependencies on PHP 8.4 (required by the current locked development dependencies), builds the React/Vite frontend, runs the Laravel/Pest suite, and executes `php artisan tradeon:smoke`.

When those steps succeed, Playwright starts a headless Chromium browser against the local Laravel server. It captures the real rendered Landing, Login, Register, Explore and Pricing pages, then creates a synthetic test account and company through the registration/onboarding forms, publishes a fictional feed post, and captures the feed, requests and billing screens at desktop/mobile widths.

## Where results are saved

Each run uploads an artifact named `tradeon-verification-<run number>` with:
- `vite-build.log`: Vite production build output.
- `phpunit.log`: Laravel/Pest output.
- `smoke.log`: `tradeon:smoke` output.
- `laravel-server.log`: local test server output.
- `screenshots/*.png`: browser screenshots from that exact run.

Artifacts are retained for 30 days. Open the repository's **Actions → Laravel build, tests and browser screenshots** workflow, choose the latest run, then download the verification artifact. The browser screenshots are real captures of a locally running CI instance, not production screenshots.

## Environment / secrets

- A real `.env` is intentionally not committed. `.gitignore` excludes `.env` and `.env.production`.
- CI starts from `.env.example`, generates an ephemeral `APP_KEY`, and uses SQLite plus file/array local services.
- No Paddle API key, client token, webhook secret, production database password, SMTP password, or real customer data is required by this workflow.
- Registration and the sample company/post use synthetic data inside the disposable runner only. They are not committed to the application database and are not pushed to a production host.
- The workflow's screenshots represent the current UI at the commit being tested. They are not evidence of configured real external services or a full-stack public production deployment.

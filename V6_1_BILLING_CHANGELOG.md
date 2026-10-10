# Tradeon v6.1 Billing Add-on

Implemented:

- Subscription plans and feature limits.
- Monthly and yearly billing intervals.
- Local subscription state machine.
- Billing invoices and provider payload storage.
- Idempotent webhook event storage.
- Paddle webhook signature verification.
- Paddle.js checkout integration using a client-side token only.
- Pricing page and account billing/invoices page.
- Free plan activation without a payment provider.
- Provider price IDs kept in configuration/database, not hardcoded in the UI.
- Automatic cancellation of previous active/pending subscriptions when a new subscription becomes active.
- Feature-ready billing provider service for future provider changes.
- Feature tests for pricing, authentication, checkout creation, and webhook signature rejection.

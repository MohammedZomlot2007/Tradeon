# Tradeon Billing Setup

Tradeon uses Paddle Billing for subscription management.

## Database

Run:

```bash
php artisan migrate
php artisan db:seed
```

`DatabaseSeeder` remains production-safe: it only creates billing plans and an admin when the explicit admin environment variables are supplied.

## Paddle configuration

Create a Paddle product and price in the Paddle dashboard, then put the resulting price ID into `.env`:

```env
BILLING_PROVIDER=paddle
BILLING_CURRENCY=USD
PADDLE_ENV=sandbox
PADDLE_API_URL=https://sandbox-api.paddle.com
PADDLE_CLIENT_TOKEN=test_...
PADDLE_WEBHOOK_SECRET=pdl_ntfset_...

PADDLE_PRO_MONTHLY_PRICE_ID=pri_...
```

The PRO plan is $5 USD/month, billed monthly. No other plans are available.

Configure a Paddle notification destination to:

```text
POST /billing/webhooks/paddle
```

The webhook endpoint verifies the `Paddle-Signature` header using the raw request body and a five-minute replay window. Payment access is updated from verified server-side webhook events, not from the browser checkout callback.

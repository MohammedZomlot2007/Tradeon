<?php

namespace App\Services\Billing;

use App\Models\BillingInvoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaddleBillingService
{
    public function clientToken(): ?string
    {
        return config('billing.paddle.client_token');
    }

    public function providerConfigured(): bool
    {
        return config('billing.provider') === 'paddle' && filled($this->clientToken());
    }

    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        $secret = config('billing.paddle.webhook_secret');
        if (! $secret || ! $signature) {
            return false;
        }

        $parts = collect(explode(';', $signature))->mapWithKeys(function (string $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);

            return [$key => $value];
        });

        $timestamp = (int) ($parts->get('ts') ?? 0);
        $provided = (string) ($parts->get('h1') ?? '');

        if (! $timestamp || ! $provided || abs(now()->timestamp - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.':'.$rawBody, $secret);

        return hash_equals($expected, $provided);
    }

    public function applyWebhook(string $eventType, array $data): void
    {
        $subscriptionId = data_get($data, 'id');
        $providerSubscriptionId = data_get($data, 'id');

        if (str_starts_with($eventType, 'subscription.') && $providerSubscriptionId) {
            $customData = (array) data_get($data, 'custom_data', []);
            $tradeonUserId = data_get($customData, 'tradeon_user_id');

            $user = $tradeonUserId ? User::find($tradeonUserId) : null;

            if (! $user) {
                $email = data_get($data, 'customer.email');
                $user = $email ? User::where('email', $email)->first() : null;
            }

            if (! $user) {
                return;
            }

            $priceId = data_get($data, 'items.0.price.id') ?: data_get($data, 'items.0.price_id');
            $plan = $priceId ? SubscriptionPlan::where('provider_monthly_price_id', $priceId)
                ->orWhere('provider_yearly_price_id', $priceId)->first() : null;

            if (! $plan) {
                return;
            }

            $subscription = Subscription::where('provider_subscription_id', $providerSubscriptionId)->first()
                ?? Subscription::where('user_id', $user->id)
                    ->where('subscription_plan_id', $plan->id)
                    ->where('status', 'pending')
                    ->first()
                ?? Subscription::firstOrNew(['provider_subscription_id' => $providerSubscriptionId]);

            $subscription->user_id = $user->id;
            $subscription->subscription_plan_id = $plan->id;
            $subscription->provider = 'paddle';
            $subscription->provider_subscription_id = $providerSubscriptionId;
            $subscription->status = $this->mapSubscriptionStatus((string) data_get($data, 'status', 'active'));
            $subscription->provider_customer_id = data_get($data, 'customer_id') ?? $subscription->provider_customer_id;
            $subscription->provider_price_id = data_get($data, 'items.0.price.id') ?? $subscription->provider_price_id;
            $subscription->billing_interval = data_get($data, 'items.0.price.billing_cycle.interval') ?? $subscription->billing_interval;
            $subscription->trial_ends_at = data_get($data, 'scheduled_change.effective_at') ? null : $subscription->trial_ends_at;
            $subscription->starts_at = $subscription->starts_at ?: now();
            $subscription->renews_at = data_get($data, 'next_billed_at');
            $subscription->cancelled_at = $subscription->status === 'cancelled' ? ($subscription->cancelled_at ?: now()) : null;
            $subscription->provider_payload = $data;
            $subscription->save();

            if (in_array($subscription->status, ['active', 'trialing'], true)) {
                Subscription::where('user_id', $subscription->user_id)
                    ->whereKeyNot($subscription->id)
                    ->whereIn('status', ['active', 'trialing', 'pending'])
                    ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'ends_at' => now()]);
            }
        }

        if (str_starts_with($eventType, 'transaction.')) {
            $subscription = null;
            $subscriptionProviderId = data_get($data, 'subscription_id');
            if ($subscriptionProviderId) {
                $subscription = Subscription::where('provider_subscription_id', $subscriptionProviderId)->first();
            }
            $user = $subscription?->user;
            if (! $user && ($email = data_get($data, 'customer.email'))) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                $status = $this->mapInvoiceStatus((string) data_get($data, 'status', 'billed'));
                $invoiceId = data_get($data, 'invoice_id') ?: data_get($data, 'id');
                $totals = data_get($data, 'details.totals', []);
                BillingInvoice::updateOrCreate(
                    ['provider_invoice_id' => $invoiceId],
                    [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription?->id,
                        'provider' => 'paddle',
                        'invoice_number' => 'TRD-'.strtoupper(substr((string) $invoiceId, -10)),
                        'status' => $status,
                        'currency' => strtoupper((string) data_get($data, 'currency_code', config('billing.default_currency'))),
                        'subtotal' => $this->minorToMajor(data_get($totals, 'subtotal', 0)),
                        'tax' => $this->minorToMajor(data_get($totals, 'tax', 0)),
                        'total' => $this->minorToMajor(data_get($totals, 'total', 0)),
                        'issued_at' => data_get($data, 'billed_at') ? now()->parse(data_get($data, 'billed_at')) : now(),
                        'paid_at' => $eventType === 'transaction.completed' ? now() : null,
                        'pdf_url' => data_get($data, 'invoice_pdf'),
                        'provider_payload' => $data,
                    ]
                );
            }
        }

        Log::info('Tradeon billing webhook applied', ['event_type' => $eventType, 'provider_id' => $subscriptionId]);
    }

    private function mapSubscriptionStatus(string $status): string
    {
        return match ($status) {
            'active' => 'active',
            'trialing' => 'trialing',
            'past_due' => 'past_due',
            'paused' => 'paused',
            'canceled', 'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    private function mapInvoiceStatus(string $status): string
    {
        return match ($status) {
            'completed', 'billed', 'paid' => 'paid',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'cancelled',
            default => 'open',
        };
    }

    private function minorToMajor(int|string|float|null $value): float
    {
        return ((float) ($value ?? 0)) / 100;
    }
}

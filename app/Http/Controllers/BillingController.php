<?php

namespace App\Http\Controllers;

use App\Models\BillingInvoice;
use App\Models\BillingWebhookEvent;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Billing\PaddleBillingService;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function pricing()
    {
        return Inertia::render('Billing/Pricing', [
            'plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'billing' => [
                'provider' => config('billing.provider'),
                'configured' => app(PaddleBillingService::class)->providerConfigured(),
                'client_token' => config('billing.paddle.client_token'),
                'environment' => config('billing.paddle.environment'),
            ],
        ]);
    }

    public function checkout(Request $request, SubscriptionPlan $plan)
    {
        abort_unless($plan->is_active, 404);

        $priceId = $plan->provider_monthly_price_id;

        if (! filled($priceId)) {
            return back()->with('error', __('flash.plan_not_configured'));
        }

        Subscription::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->delete();

        Subscription::create([
            'user_id' => $request->user()->id,
            'subscription_plan_id' => $plan->id,
            'provider' => 'paddle',
            'provider_price_id' => $priceId,
            'billing_interval' => 'monthly',
            'status' => 'pending',
        ]);

        return back()->with('billing_checkout', [
            'price_id' => $priceId,
            'email' => $request->user()->email,
            'name' => $request->user()->name,
            'plan' => $plan->slug,
            'interval' => 'monthly',
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Billing/Index', [
            'subscription' => $user->activeSubscription()->with('plan')->first(),
            'invoices' => BillingInvoice::where('user_id', $user->id)->latest('issued_at')->limit(25)->get(),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get(),
            'storage' => app(StorageQuotaService::class)->usageFor($user),
            'billing' => [
                'provider' => config('billing.provider'),
                'configured' => app(PaddleBillingService::class)->providerConfigured(),
            ],
        ]);
    }

    public function webhook(Request $request, PaddleBillingService $billing)
    {
        $rawBody = $request->getContent();
        $signature = $request->header('Paddle-Signature', '');

        abort_unless($billing->verifyWebhook($rawBody, $signature), 400, 'Invalid billing webhook signature.');

        $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        $eventId = (string) data_get($payload, 'event_id', data_get($payload, 'id', Str::uuid()->toString()));

        $event = BillingWebhookEvent::firstOrCreate(
            ['event_id' => $eventId],
            [
                'provider' => 'paddle',
                'event_type' => (string) data_get($payload, 'event_type', ''),
                'payload' => $payload,
            ]
        );

        if ($event->processed_at) {
            return response()->json(['ok' => true]);
        }

        try {
            $billing->applyWebhook((string) data_get($payload, 'event_type', ''), (array) data_get($payload, 'data', []));
            $event->update(['status' => 'processed', 'processed_at' => now(), 'processing_error' => null]);
        } catch (\Throwable $e) {
            report($e);
            $event->update(['status' => 'failed', 'processing_error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }
}

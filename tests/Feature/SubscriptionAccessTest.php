<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

function makeProPlan(): SubscriptionPlan
{
    return SubscriptionPlan::firstOrCreate(
        ['slug' => 'pro'],
        [
            'name' => 'PRO',
            'monthly_price' => 5.00,
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 1,
        ]
    );
}

function makeSubscription(User $user, string $status): Subscription
{
    return Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => makeProPlan()->id,
        'provider' => 'paddle',
        'provider_subscription_id' => 'sub_'.$user->id.'_'.$status,
        'status' => $status,
        'starts_at' => now(),
        'renews_at' => now()->addMonth(),
    ]);
}

it('redirects an authenticated unpaid user from a protected route to pricing with a flash message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/biz-dashboard')
        ->assertRedirect('/pricing')
        ->assertSessionHas('error', EnsureActiveSubscription::FLASH_MESSAGE);
});

it('blocks an authenticated unpaid user from the feed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/feed')
        ->assertRedirect('/pricing')
        ->assertSessionHas('error');
});

it('allows an authenticated active subscriber to access protected routes', function () {
    $user = User::factory()->create();
    makeSubscription($user, 'active');

    $this->actingAs($user)->get('/biz-dashboard')->assertOk();
    $this->actingAs($user)->get('/feed')->assertOk();
    $this->actingAs($user)->get(route('products.index'))->assertOk();
});

it('blocks every non-active subscription status', function (string $status) {
    $user = User::factory()->create();
    makeSubscription($user, $status);

    $this->actingAs($user)->get('/biz-dashboard')
        ->assertRedirect('/pricing')
        ->assertSessionHas('error', EnsureActiveSubscription::FLASH_MESSAGE);
})->with(['pending', 'trialing', 'past_due', 'paused', 'canceled', 'cancelled']);

it('returns a 403 json response for unpaid users on protected resources', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/biz-dashboard')
        ->assertForbidden()
        ->assertJson(['message' => 'You need an active subscription to access this resource.']);

    $this->actingAs($user)->postJson('/api/smart-match')
        ->assertForbidden()
        ->assertJson(['message' => 'You need an active subscription to access this resource.']);
});

it('keeps pricing and billing accessible to unpaid users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/pricing')->assertOk();
    $this->actingAs($user)->get('/billing')->assertOk();
});

it('still redirects guests to login for protected routes', function () {
    $this->get('/biz-dashboard')->assertRedirect('/login');
});

it('keeps the paddle webhook reachable without a csrf token', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $payload = [
        'event_id' => 'evt_test_1',
        'event_type' => 'subscription.created',
        'data' => [
            'id' => 'sub_test_1',
            'status' => 'active',
            'customer' => ['email' => 'buyer@example.com'],
            'items' => [['price' => ['id' => 'pri_test', 'billing_cycle' => ['interval' => 'month']]]],
        ],
    ];

    $rawBody = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$rawBody, 'test-secret');

    $response = $this->call('POST', '/billing/webhooks/paddle', [], [], [], [
        'HTTP_Paddle-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody);

    $response->assertOk();
    expect($response->json('ok'))->toBeTrue();
});

it('still rejects paddle webhook requests with an invalid signature', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $this->postJson('/billing/webhooks/paddle', ['event_type' => 'subscription.created'])
        ->assertStatus(400);
});

it('updates a pending subscription to active when subscription.created webhook arrives with custom_data tradeon_user_id', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 20.00,
        'currency' => 'USD', 'provider_monthly_price_id' => 'pri_pro_monthly',
        'is_active' => true,
    ]);

    $pending = Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'provider' => 'paddle',
        'provider_price_id' => 'pri_pro_monthly',
        'billing_interval' => 'monthly',
        'status' => 'pending',
    ]);

    $payload = [
        'event_id' => 'evt_test_pending_to_active',
        'event_type' => 'subscription.created',
        'data' => [
            'id' => 'sub_paddle_123',
            'status' => 'active',
            'customer_id' => 'ctm_123',
            'custom_data' => [
                'tradeon_user_id' => $user->id,
                'plan' => 'pro',
                'interval' => 'monthly',
            ],
            'items' => [['price' => ['id' => 'pri_pro_monthly', 'billing_cycle' => ['interval' => 'month']]]],
            'next_billed_at' => '2026-10-29T00:00:00Z',
        ],
    ];

    $rawBody = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$rawBody, 'test-secret');

    $this->call('POST', '/billing/webhooks/paddle', [], [], [], [
        'HTTP_Paddle-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody)->assertOk();

    $pending->refresh();
    expect($pending->status)->toBe('active');
    expect($pending->provider_subscription_id)->toBe('sub_paddle_123');
    expect($pending->renews_at)->not->toBeNull();
    expect(Subscription::where('user_id', $user->id)->count())->toBe(1);
});

it('creates a new active subscription when no pending subscription exists for the user', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 20.00,
        'currency' => 'USD', 'provider_monthly_price_id' => 'pri_pro_monthly',
        'is_active' => true,
    ]);

    $payload = [
        'event_id' => 'evt_test_create_new',
        'event_type' => 'subscription.created',
        'data' => [
            'id' => 'sub_paddle_456',
            'status' => 'active',
            'customer_id' => 'ctm_456',
            'custom_data' => [
                'tradeon_user_id' => $user->id,
                'plan' => 'pro',
                'interval' => 'monthly',
            ],
            'items' => [['price' => ['id' => 'pri_pro_monthly', 'billing_cycle' => ['interval' => 'month']]]],
            'next_billed_at' => '2026-10-29T00:00:00Z',
        ],
    ];

    $rawBody = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$rawBody, 'test-secret');

    $this->call('POST', '/billing/webhooks/paddle', [], [], [], [
        'HTTP_Paddle-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody)->assertOk();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'status' => 'active',
        'provider_subscription_id' => 'sub_paddle_456',
    ]);
});

it('updates the same subscription on subscription.updated webhook', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 20.00,
        'currency' => 'USD', 'provider_monthly_price_id' => 'pri_pro_monthly',
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'provider' => 'paddle',
        'provider_subscription_id' => 'sub_paddle_789',
        'provider_price_id' => 'pri_pro_monthly',
        'billing_interval' => 'monthly',
        'status' => 'active',
    ]);

    $payload = [
        'event_id' => 'evt_test_updated',
        'event_type' => 'subscription.updated',
        'data' => [
            'id' => 'sub_paddle_789',
            'status' => 'active',
            'customer_id' => 'ctm_789',
            'custom_data' => [
                'tradeon_user_id' => $user->id,
                'plan' => 'pro',
                'interval' => 'monthly',
            ],
            'items' => [['price' => ['id' => 'pri_pro_monthly', 'billing_cycle' => ['interval' => 'month']]]],
            'next_billed_at' => '2026-11-29T00:00:00Z',
        ],
    ];

    $rawBody = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$rawBody, 'test-secret');

    $this->call('POST', '/billing/webhooks/paddle', [], [], [], [
        'HTTP_Paddle-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody)->assertOk();

    $subscription->refresh();
    expect($subscription->status)->toBe('active');
    expect($subscription->renews_at)->not->toBeNull();
    expect(Subscription::where('user_id', $user->id)->count())->toBe(1);
});

it('handles invalid nonexistent tradeon_user_id safely without creating a subscription', function () {
    config(['billing.paddle.webhook_secret' => 'test-secret']);

    $plan = SubscriptionPlan::create([
        'name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 20.00,
        'currency' => 'USD', 'provider_monthly_price_id' => 'pri_pro_monthly',
        'is_active' => true,
    ]);

    $payload = [
        'event_id' => 'evt_test_invalid_user',
        'event_type' => 'subscription.created',
        'data' => [
            'id' => 'sub_paddle_000',
            'status' => 'active',
            'customer_id' => 'ctm_000',
            'custom_data' => [
                'tradeon_user_id' => 99999,
                'plan' => 'pro',
                'interval' => 'monthly',
            ],
            'items' => [['price' => ['id' => 'pri_pro_monthly', 'billing_cycle' => ['interval' => 'month']]]],
        ],
    ];

    $rawBody = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$rawBody, 'test-secret');

    $this->call('POST', '/billing/webhooks/paddle', [], [], [], [
        'HTTP_Paddle-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody)->assertOk();

    $this->assertDatabaseMissing('subscriptions', [
        'provider_subscription_id' => 'sub_paddle_000',
    ]);
});

it('scopes the subscription check to the authenticated user company', function () {
    $activeUser = User::factory()->create();
    $lockedUser = User::factory()->create();

    makeSubscription($activeUser, 'active');
    makeSubscription($lockedUser, 'past_due');

    // The locked company stays locked.
    $this->actingAs($lockedUser)->get('/biz-dashboard')->assertRedirect('/pricing');

    // The other company's backend is unaffected.
    $this->actingAs($activeUser)->get('/biz-dashboard')->assertOk();
});

it('unlocks the backend again as soon as the subscription becomes active', function () {
    $user = User::factory()->create();
    $subscription = makeSubscription($user, 'past_due');

    $this->actingAs($user)->get('/biz-dashboard')->assertRedirect('/pricing');

    $subscription->update(['status' => 'active']);

    $this->actingAs($user)->get('/biz-dashboard')->assertOk();
});

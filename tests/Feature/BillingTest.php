<?php

use App\Models\SubscriptionPlan;
use App\Models\User;

it('shows active pricing plans', function () {
    SubscriptionPlan::create(['name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 5.00, 'currency' => 'USD', 'features' => [], 'limits' => [], 'is_active' => true]);
    $this->get('/pricing')->assertOk();
});

it('requires authentication for checkout', function () {
    $plan = SubscriptionPlan::create(['name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 5.00, 'currency' => 'USD', 'is_active' => true]);
    $this->post('/billing/checkout/'.$plan->id, ['interval' => 'monthly'])->assertRedirect('/login');
});

it('creates a pending checkout subscription for an authenticated user', function () {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::create([
        'name' => 'PRO', 'slug' => 'pro', 'monthly_price' => 5.00,
        'currency' => 'USD', 'provider_monthly_price_id' => 'pri_test_monthly', 'is_active' => true,
    ]);

    $this->actingAs($user)->post('/billing/checkout/'.$plan->id, ['interval' => 'monthly'])
        ->assertSessionHas('billing_checkout');

    $this->assertDatabaseHas('subscriptions', ['user_id' => $user->id, 'status' => 'pending', 'provider_price_id' => 'pri_test_monthly']);
});

it('rejects invalid paddle webhook signatures', function () {
    config(['billing.paddle.webhook_secret' => 'secret']);
    $this->postJson('/billing/webhooks/paddle', ['event_type' => 'subscription.created'])->assertStatus(400);
});

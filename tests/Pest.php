<?php

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

uses()->beforeEach(function () {
    // Inertia page tests must not depend on a fresh Vite build artifact.
    $this->withoutVite();
})->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Grant the user an active PRO subscription so tests can reach
 * routes protected by the EnsureActiveSubscription middleware.
 */
function giveActiveSubscription(User $user): Subscription
{
    $plan = SubscriptionPlan::firstOrCreate(
        ['slug' => 'pro'],
        [
            'name' => 'PRO',
            'monthly_price' => 5.00,
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 1,
        ]
    );

    return Subscription::create([
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
        'provider' => 'paddle',
        'provider_subscription_id' => 'sub_test_'.$user->id,
        'status' => 'active',
        'starts_at' => now(),
        'renews_at' => now()->addMonth(),
    ]);
}

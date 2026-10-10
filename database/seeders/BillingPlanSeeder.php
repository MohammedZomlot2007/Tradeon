<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class BillingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $freeMb = (int) config('billing.free_storage_mb', 30);

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'FREE',
                'description' => 'Free workspace for every business, with limited publishing storage.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'USD',
                'provider_monthly_price_id' => null,
                'provider_yearly_price_id' => null,
                'features' => [
                    'Company page',
                    'Business networking',
                    $freeMb.' MB publishing storage',
                ],
                'limits' => ['team_members' => 1, 'products' => 10, 'rfqs' => 5, 'storage_mb' => $freeMb],
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'PRO',
                'description' => 'Full access to all Tradeon features.',
                'monthly_price' => 20.00,
                'yearly_price' => 0,
                'currency' => 'USD',
                'provider_monthly_price_id' => env('PADDLE_PRO_MONTHLY_PRICE_ID'),
                'provider_yearly_price_id' => null,
                'features' => [
                    'Unlimited business networking',
                    'Advanced sourcing tools',
                    'Priority discovery',
                    'Analytics & insights',
                    'Verified business badge',
                    'Unlimited publishing storage',
                ],
                'limits' => ['team_members' => -1, 'products' => -1, 'rfqs' => -1, 'storage_mb' => -1],
                'trial_days' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        SubscriptionPlan::whereNotIn('slug', ['pro', 'free'])->update(['is_active' => false]);
    }
}

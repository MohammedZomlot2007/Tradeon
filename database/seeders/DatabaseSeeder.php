<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Safe defaults only: billing plans + optional env-based admin bootstrap.
        // Demo content (BizLinkSeeder) is NEVER called here. Run it explicitly
        // in local development only: php artisan db:seed --class=BizLinkSeeder
        $this->call(BillingPlanSeeder::class);
        $this->call(AdminSeeder::class);
    }
}

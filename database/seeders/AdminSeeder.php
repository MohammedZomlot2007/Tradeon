<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Bootstrap the first administrator account.
 *
 * Credentials are ONLY read from the environment. Nothing is created
 * when the variables are absent, so a plain `php artisan db:seed`
 * never provisions privileged access by accident.
 *
 *   TRADEON_ADMIN_NAME="Site Administrator"
 *   TRADEON_ADMIN_EMAIL="admin@example.com"
 *   TRADEON_ADMIN_PASSWORD="use-a-long-random-secret"
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('TRADEON_ADMIN_EMAIL');
        $password = env('TRADEON_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('TRADEON_ADMIN_NAME', 'Tradeon Administrator'),
                'username' => env('TRADEON_ADMIN_USERNAME'),
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}

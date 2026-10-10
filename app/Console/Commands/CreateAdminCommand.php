<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'tradeon:admin
        {--name= : Full name of the administrator}
        {--username= : Username used for login}
        {--email= : Login email address}
        {--password= : Password (will be prompted securely when omitted)}
        {--force : Overwrite an existing account with the same email}';

    protected $description = 'Create or promote a Tradeon administrator account';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Admin full name');
        $email = $this->option('email') ?: $this->ask('Admin email');
        $username = $this->option('username') ?: $this->ask('Admin username (optional)');
        $password = $this->option('password') ?: $this->secret('Admin password (min 10 characters)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing && ! $this->option('force')) {
            $this->error('An account with this email already exists. Re-run with --force to promote/update it.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username ?: $existing?->username,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->info("Administrator ready: {$user->name} <{$user->email}>");

        return self::SUCCESS;
    }
}

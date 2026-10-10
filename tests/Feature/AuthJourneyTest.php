<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function journeyCompany(User $user): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => 'Journey Co '.$user->id,
        'slug' => 'journey-co-'.$user->id,
        'country' => 'Palestine',
        'city' => 'Ramallah',
        'business_types' => ['Retailer'],
        'industry' => 'Other',
    ]);
}

it('routes admins to the admin area on login', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.index', absolute: false));
});

it('routes users with a company to the feed on login', function () {
    $user = User::factory()->create();
    journeyCompany($user);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/feed');
});

it('routes users without a company to onboarding on login', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('onboarding.company', absolute: false));
});

it('completes onboarding with a welcome progress page', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);

    $this->actingAs($user)->get('/welcome')->assertRedirect(route('onboarding.company', absolute: false));

    $company = journeyCompany($user);

    $this->actingAs($user)->get('/welcome')->assertOk();
    expect($company->fresh())->not->toBeNull();
});

it('generates a unique username at registration', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'business_types' => ['Buyer'],
        'industry' => 'Technology',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user->username)->not->toBeNull();
});

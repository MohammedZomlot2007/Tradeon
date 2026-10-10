<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a company profile view for another visitor', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $company = Company::create([
        'user_id' => $owner->id,
        'name' => 'Acme Test Company',
        'slug' => 'acme-test-company-'.uniqid(),
        'country' => 'Palestine',
        'city' => 'Nablus',
        'business_types' => ['Buyer'],
        'industry' => 'Technology',
        'profile_views' => 0,
    ]);

    $this->actingAs($visitor)->get(route('companies.show', $company->slug))->assertOk();

    expect($company->fresh()->profile_views)->toBe(1);
});

it('does not count the owner viewing their own profile', function () {
    $owner = User::factory()->create();
    $company = Company::create([
        'user_id' => $owner->id,
        'name' => 'Acme Test Company',
        'slug' => 'acme-test-company-'.uniqid(),
        'country' => 'Palestine',
        'city' => 'Nablus',
        'business_types' => ['Buyer'],
        'industry' => 'Technology',
        'profile_views' => 0,
    ]);

    $this->actingAs($owner)->get(route('companies.show', $company->slug))->assertOk();

    expect($company->fresh()->profile_views)->toBe(0);
});

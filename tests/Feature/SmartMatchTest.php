<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns deterministic match scores based on company data', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $company = Company::create([
        'user_id' => $user->id,
        'name' => 'Palestine Textile Factory',
        'slug' => 'palestine-textile-factory',
        'country' => 'Palestine',
        'city' => 'Hebron',
        'business_types' => ['Manufacturer'],
        'industry' => 'Fashion & Apparel',
        'export_markets' => ['UAE'],
        'response_rate' => 95,
        'verified_tier' => 'factory_verified',
    ]);

    $response = $this->actingAs($user)->postJson(route('smart-match.match'), [
        'need_type' => 'Manufacturer',
        'industry' => 'Fashion',
        'target_country' => 'UAE',
        'prompt' => 'textile factory apparel',
    ])->assertOk();

    expect($response->json('matches.0.company.id'))->toBe($company->id)
        ->and($response->json('matches.0.match_score'))->toBeGreaterThan(70);
});

<?php

use App\Models\Company;
use App\Models\Factory;
use App\Models\ProductShowcase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function managedCompany(User $user, string $suffix = ''): Company
{
    return Company::create([
        'user_id' => $user->id,
        'name' => 'Managed Co '.$user->id.$suffix,
        'slug' => 'managed-co-'.$user->id.$suffix,
        'country' => 'Turkey',
        'city' => 'Istanbul',
        'business_types' => ['Manufacturer'],
        'industry' => 'Manufacturing',
    ]);
}

it('lets owners create and update their factory profile', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    managedCompany($user);

    $this->actingAs($user)->post('/factory', [
        'name' => 'Main Plant',
        'factory_type' => 'OEM',
        'production_lines' => 4,
        'annual_output' => '200,000 units',
    ])->assertRedirect();

    expect(Factory::count())->toBe(1);

    $factory = Factory::first();
    $this->actingAs($user)->put("/factory/{$factory->id}", [
        'name' => 'Main Plant Updated',
        'production_lines' => 6,
    ])->assertRedirect();

    expect($factory->fresh()->production_lines)->toBe(6);
});

it('blocks duplicate factory profiles and non-owners', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    managedCompany($user);
    Factory::create(['company_id' => $user->company->id, 'name' => 'Plant']);

    $this->actingAs($user)->post('/factory', ['name' => 'Second'])->assertConflict();

    $stranger = User::factory()->create();
    giveActiveSubscription($stranger);
    managedCompany($stranger, '-b');
    $factory = Factory::first();
    $this->actingAs($stranger)->put("/factory/{$factory->id}", ['name' => 'Hijack'])->assertForbidden();
    $this->actingAs($stranger)->delete("/factory/{$factory->id}")->assertForbidden();
});

it('lets owners manage product showcases', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    managedCompany($user);

    $this->actingAs($user)->post('/showcases', [
        'title' => 'Steel Beam S400',
        'description' => 'High-grade structural steel.',
        'industry' => 'Construction',
        'moq' => '100 units',
    ])->assertRedirect();

    expect(ProductShowcase::count())->toBe(1);

    $showcase = ProductShowcase::first();
    $this->actingAs($user)->put("/showcases/{$showcase->id}", [
        'title' => 'Steel Beam S400 Pro',
        'description' => 'High-grade structural steel.',
        'industry' => 'Construction',
    ])->assertRedirect();

    expect($showcase->fresh()->title)->toBe('Steel Beam S400 Pro');

    $this->actingAs($user)->delete("/showcases/{$showcase->id}")->assertRedirect();
    expect(ProductShowcase::count())->toBe(0);
});

it('blocks showcase access for non-owners', function () {
    $owner = User::factory()->create();
    giveActiveSubscription($owner);
    managedCompany($owner);
    $showcase = ProductShowcase::create([
        'company_id' => $owner->company->id,
        'title' => 'Widget',
        'slug' => 'widget-1',
        'description' => 'A widget.',
        'industry' => 'Technology',
    ]);

    $stranger = User::factory()->create();
    giveActiveSubscription($stranger);
    managedCompany($stranger, '-s');

    $this->actingAs($stranger)->put("/showcases/{$showcase->id}", [
        'title' => 'Hijacked',
        'description' => 'x',
        'industry' => 'Technology',
    ])->assertForbidden();
});

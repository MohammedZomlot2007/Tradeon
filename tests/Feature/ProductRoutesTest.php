<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects product routes behind authentication', function () {
    $this->get(route('products.index'))->assertRedirect(route('login'));
    $this->get(route('products.create'))->assertRedirect(route('login'));
});

it('allows an authenticated user to access product catalog', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);

    $this->actingAs($user)->get(route('products.index'))->assertOk();
});

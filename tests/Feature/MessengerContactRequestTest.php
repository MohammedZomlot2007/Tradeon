<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens the contact request flow for a recipient query', function () {
    $sender = User::factory()->create();
    giveActiveSubscription($sender);
    $receiver = User::factory()->create();
    Company::create([
        'user_id' => $receiver->id,
        'name' => 'Receiver Company',
        'slug' => 'receiver-company-'.uniqid(),
        'country' => 'Palestine',
        'city' => 'Nablus',
        'business_types' => ['Supplier'],
        'industry' => 'Technology',
    ]);

    $this->actingAs($sender)
        ->get(route('messages.index', ['recipient' => $receiver->id, 'purpose' => 'catalogue']))
        ->assertOk();
});

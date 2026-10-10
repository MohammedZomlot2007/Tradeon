<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents a user from sending messages to another users conversation', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $attacker = User::factory()->create();
    giveActiveSubscription($attacker);

    $conversation = Conversation::create([
        'user_one_id' => $owner->id,
        'user_two_id' => $other->id,
    ]);

    $this->actingAs($attacker)
        ->post(route('messages.send', $conversation), ['body' => 'Intrusion'])
        ->assertNotFound();
});

it('allows a participant to send a message', function () {
    $owner = User::factory()->create();
    giveActiveSubscription($owner);
    $other = User::factory()->create();

    $conversation = Conversation::create([
        'user_one_id' => $owner->id,
        'user_two_id' => $other->id,
    ]);

    $this->actingAs($owner)
        ->post(route('messages.send', $conversation), ['body' => 'Hello'])
        ->assertRedirect();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id' => $owner->id,
        'body' => 'Hello',
    ]);
});

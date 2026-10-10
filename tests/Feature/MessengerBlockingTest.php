<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function messengerPair(): array
{
    $a = User::factory()->create();
    $b = User::factory()->create();
    giveActiveSubscription($a);
    giveActiveSubscription($b);
    $conversation = Conversation::create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
    ]);

    return [$a, $b, $conversation];
}

it('blocks messaging between blocked users', function () {
    [$a, $b, $conversation] = messengerPair();
    UserBlock::create(['user_id' => $a->id, 'blocked_user_id' => $b->id]);

    $this->actingAs($b)->post("/messages/{$conversation->id}", ['body' => 'Hello?'])->assertForbidden();
    $this->actingAs($a)->post("/messages/{$conversation->id}", ['body' => 'Hello?'])->assertForbidden();
    expect(Message::count())->toBe(0);
});

it('blocks chat requests involving blocked users', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    giveActiveSubscription($a);
    giveActiveSubscription($b);
    UserBlock::create(['user_id' => $a->id, 'blocked_user_id' => $b->id]);

    $this->actingAs($b)->post('/chat-requests', [
        'receiver_id' => $a->id, 'purpose' => 'general', 'initial_message' => 'Hi',
    ])->assertForbidden();

    $this->actingAs($a)->post('/chat-requests', [
        'receiver_id' => $b->id, 'purpose' => 'general', 'initial_message' => 'Hi',
    ])->assertForbidden();
});

it('blocks connections with blocked or suspended users', function () {
    $me = User::factory()->create();
    giveActiveSubscription($me);
    $blocked = User::factory()->create();
    UserBlock::create(['user_id' => $me->id, 'blocked_user_id' => $blocked->id]);

    $this->actingAs($me)->post("/users/{$blocked->id}/connect")->assertForbidden();

    $suspended = User::factory()->create(['status' => 'suspended']);
    $this->actingAs($me)->post("/users/{$suspended->id}/connect")->assertStatus(422);
});

it('prevents accessing another user conversation', function () {
    [$a, $b, $conversation] = messengerPair();
    $outsider = User::factory()->create();
    giveActiveSubscription($outsider);

    $this->actingAs($outsider)->post("/messages/{$conversation->id}", ['body' => 'Snoop'])->assertNotFound();
});

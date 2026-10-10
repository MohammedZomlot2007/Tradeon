<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}', fn (User $user, int $id) => $user->id === $id);
Broadcast::channel('conversations.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::query()->whereKey($conversationId)->where(fn ($q) => $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id))->exists();
});

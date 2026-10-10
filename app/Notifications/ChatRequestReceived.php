<?php

namespace App\Notifications;

use App\Models\ChatRequest;

class ChatRequestReceived extends TradeonNotification
{
    public function __construct(public ChatRequest $request) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('chat_request', 'New contact request', $this->request->sender->name.' sent you a contact request.', '/messages');
    }
}

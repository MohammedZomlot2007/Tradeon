<?php

namespace App\Notifications;

use App\Models\Message;

class ChatMessageReceived extends TradeonNotification
{
    public function __construct(public Message $message) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('message', 'New message', $this->message->sender->name.' sent you a message.', '/messages?conversation_id='.$this->message->conversation_id);
    }
}

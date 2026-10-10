<?php

namespace App\Notifications;

use App\Models\User;

class ConnectionRequested extends TradeonNotification
{
    public function __construct(public User $sender) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('connection_request', 'New connection request', $this->sender->name.' wants to connect with you.', '/network');
    }
}

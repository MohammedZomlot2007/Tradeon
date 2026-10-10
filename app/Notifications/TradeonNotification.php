<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class TradeonNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    protected function payload(string $type, string $title, string $message, ?string $url = null): array
    {
        return compact('type', 'title', 'message', 'url');
    }
}

<?php

namespace App\Notifications;

class AccountStatusUpdated extends TradeonNotification
{
    public function __construct(public string $status, public ?string $note = null) {}

    public function toArray(object $notifiable): array
    {
        $suspended = $this->status === 'suspended';

        return $this->payload(
            'account_status',
            $suspended ? 'Your account was suspended' : 'Your account was restored',
            $suspended
                ? 'Your Tradeon account has been suspended. Contact support if you believe this is a mistake.'
                : 'Your Tradeon account has been restored. Welcome back.',
            '/feed'
        );
    }
}

<?php

namespace App\Notifications;

use App\Models\Verification;

class VerificationStatusUpdated extends TradeonNotification
{
    public function __construct(public Verification $verification) {}

    public function toArray(object $notifiable): array
    {
        $approved = $this->verification->status === 'approved';

        return $this->payload(
            'verification_status',
            $approved ? 'Verification approved' : 'Verification update',
            $approved
                ? 'Your company verification was approved.'
                : 'Your company verification status is now: '.$this->verification->status.'.',
            '/verification'
        );
    }
}

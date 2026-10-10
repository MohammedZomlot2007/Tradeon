<?php

namespace App\Notifications;

use App\Models\BusinessRequest;

class BusinessRequestUpdated extends TradeonNotification
{
    public function __construct(public BusinessRequest $request, public string $eventLabel) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('business_request', 'Business request update', 'Your business request was '.$this->eventLabel.'.', '/requests');
    }
}

<?php

namespace App\Notifications;

use App\Models\RfqQuote;

class RfqQuoteReceived extends TradeonNotification
{
    public function __construct(public RfqQuote $quote) {}

    public function toArray(object $notifiable): array
    {
        return $this->payload('rfq_quote', 'New RFQ quotation', $this->quote->company->name.' submitted a quotation for your RFQ.', '/requests');
    }
}

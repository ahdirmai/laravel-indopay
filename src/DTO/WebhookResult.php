<?php

namespace IndoPay\DTO;

use IndoPay\Enums\PaymentStatus;
use Money\Money;

class WebhookResult
{
    public function __construct(
        public string $referenceId,
        public string $gatewayReferenceId,
        public PaymentStatus $status,
        public Money $amount,
        public array $rawPayload,
        public ?string $paymentType = null,
    ) {}
}

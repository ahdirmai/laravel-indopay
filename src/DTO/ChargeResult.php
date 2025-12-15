<?php

namespace IndoPay\DTO;

use IndoPay\Enums\PaymentStatus;

class ChargeResult
{
    public function __construct(
        public string $referenceId,
        public PaymentStatus $status,
        public ?string $redirectUrl = null,
        public array $rawResponse = [],
    ) {}
}

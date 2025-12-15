<?php

namespace IndoPay\DTO;

class ChargePayload
{
    public function __construct(
        public array $customerDetails,
        public array $itemDetails = [],
        public array $customParameters = [],
        public ?string $returnUrl = null,
    ) {}
}

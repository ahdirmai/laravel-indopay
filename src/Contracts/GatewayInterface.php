<?php

namespace IndoPay\Contracts;

use Illuminate\Http\Request;
use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\DTO\WebhookResult;
use Money\Money;

interface GatewayInterface
{
    /**
     * Charge a customer.
     *
     * @param  Money  $amount
     * @param  ChargePayload  $payload
     * @return ChargeResult
     */
    public function charge(Money $amount, ChargePayload $payload): ChargeResult;

    /**
     * Parse the incoming webhook request into a standardized result.
     *
     * @param  Request  $request
     * @return WebhookResult
     */
    public function parseWebhook(Request $request): WebhookResult;

    /**
     * Verify the signature of the incoming webhook request.
     *
     * @param  Request  $request
     * @return bool
     */
    public function verifySignature(Request $request): bool;
}

<?php

namespace IndoPay\Drivers;

use Illuminate\Http\Request;
use IndoPay\Contracts\GatewayInterface;
use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\DTO\WebhookResult;
use IndoPay\Enums\PaymentStatus;
use Money\Money;

class XenditDriver implements GatewayInterface
{
    public function getName(): string
    {
        return 'xendit';
    }

    public function charge(Money $amount, ChargePayload $payload): ChargeResult
    {
        // Placeholder for Xendit Invoice API call
        // $params = [ ... ];
        // $invoice = \Xendit\Invoice::create($params);

        return new ChargeResult(
            referenceId: 'generated-ref-id',
            status: PaymentStatus::PENDING,
            redirectUrl: 'https://checkout-staging.xendit.co/web/...' // Mock URL
        );
    }

    public function parseWebhook(Request $request): WebhookResult
    {
        // Placeholder implementation
        $data = $request->all();
        
        $status = match ($data['status'] ?? '') {
            'PAID', 'SETTLED' => PaymentStatus::PAID,
            'EXPIRED' => PaymentStatus::EXPIRED,
            'FAILED' => PaymentStatus::FAILED,
            default => PaymentStatus::PENDING,
        };

        return new WebhookResult(
            referenceId: $data['external_id'] ?? 'unknown',
            gatewayReferenceId: $data['id'] ?? 'unknown',
            status: $status,
            amount: Money::IDR($data['amount'] ?? 0),
            rawPayload: $data
        );
    }

    public function verifySignature(Request $request): bool
    {
        // Placeholder signature verification
        // $callbackToken = config('indopay.xendit.callback_token');
        // Check header...
        return true;
    }
}

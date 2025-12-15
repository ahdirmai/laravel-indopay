<?php

namespace IndoPay\Drivers;

use Illuminate\Http\Request;
use IndoPay\Contracts\GatewayInterface;
use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\DTO\WebhookResult;
use IndoPay\Enums\PaymentStatus;
use Money\Money;

class MidtransDriver implements GatewayInterface
{
    public function getName(): string
    {
        return 'midtrans';
    }

    public function charge(Money $amount, ChargePayload $payload): ChargeResult
    {
        // Placeholder for Midtrans Snap API call
        // $params = [ ... ];
        // $snapToken = Snap::getSnapToken($params);

        return new ChargeResult(
            referenceId: 'generated-ref-id', // This should match what we sent or what they returned
            status: PaymentStatus::PENDING,
            redirectUrl: 'https://app.sandbox.midtrans.com/snap/v2/vtweb/token' // Mock URL
        );
    }

    public function parseWebhook(Request $request): WebhookResult
    {
        // Placeholder implementation
        $data = $request->all();
        
        $status = match ($data['transaction_status'] ?? '') {
            'capture', 'settlement' => PaymentStatus::PAID,
            'deny', 'cancel', 'expire' => PaymentStatus::FAILED,
            'pending' => PaymentStatus::PENDING,
            default => PaymentStatus::PENDING,
        };

        return new WebhookResult(
            referenceId: $data['order_id'] ?? 'unknown',
            gatewayReferenceId: $data['transaction_id'] ?? 'unknown',
            status: $status,
            amount: Money::IDR($data['gross_amount'] ?? 0), // Assuming MoneyPHP factory
            rawPayload: $data
        );
    }

    public function verifySignature(Request $request): bool
    {
        // Placeholder signature verification
        // $serverKey = config('indopay.midtrans.server_key');
        // $signatureKey = $request->input('signature_key');
        // Check hash...
        return true;
    }
}

<?php

namespace IndoPay\Services;

use IndoPay\Contracts\GatewayInterface;
use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\Models\Transaction;
use Money\Money;
use RuntimeException;

class PaymentService
{
    public function __construct(
        protected TransactionRecorder $recorder
    ) {}

    /**
     * Initiate a charge.
     * 
     * @param  GatewayInterface $gatewayDriver
     * @param  mixed $billable  The user/model being charged
     * @param  Money $amount
     * @param  ChargePayload $payload
     * @return ChargeResult
     */
    public function charge(GatewayInterface $gatewayDriver, mixed $billable, Money $amount, ChargePayload $payload): ChargeResult
    {
        // 1. Resolve Gateway Name
        $gatewayName = method_exists($gatewayDriver, 'getName') 
            ? $gatewayDriver->getName() 
            : class_basename($gatewayDriver);

        // 2. Create Transaction (Pending)
        $transaction = $this->recorder->create(
            get_class($billable),
            $billable->getKey(),
            $gatewayName,
            (int) $amount->getAmount(),
            (array) $payload
        );

        // 3. Inject Reference ID into Payload for Gateway
        // We pass the generated reference_id to the gateway so they can link it.
        $payload->customParameters['reference_id'] = $transaction->reference_id;

        // 4. Call Gateway
        $result = $gatewayDriver->charge($amount, $payload);

        // 5. Allow Gateway to Override/Augment Reference ID?
        // Ideally, we keep our reference_id as the source of truth for the 'reference_id' column.
        // The gateway might return its own ID (e.g. 'ch_123'), which we can store in 'gateway_event_id' or 'payload'.
        
        // If the result contains a useful gateway reference (e.g. transaction ID), we can update the transaction.
        // The ChargeResult has 'referenceId'. In most gateways, this mimics our order ID, but sometimes it's their ID.
        // We will stick to the TransactionRecorder's generated ID as the primary 'reference_id'.
        
        // Optional: Save gateway response metadata if needed
        // $transaction->update(['payload' => array_merge($transaction->payload, ['gateway_response' => $result->rawResponse])]);

        return $result;
    }
}

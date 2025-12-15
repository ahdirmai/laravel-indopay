<?php

namespace IndoPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use IndoPay\Contracts\GatewayInterface;
use IndoPay\Models\Transaction;
use IndoPay\Services\TransactionRecorder;
use IndoPay\Enums\PaymentStatus;
use IndoPay\Events\PaymentSucceeded;
use IndoPay\Events\PaymentFailed;
use Exception;

class WebhookController extends Controller
{
    public function __construct(
        protected TransactionRecorder $recorder
    ) {}

    public function handle(Request $request, string $driver)
    {
        // 1. Resolve Driver
        $gatewayDriver = $this->resolveDriver($driver);

        // 2. Verify Signature
        if (! $gatewayDriver->verifySignature($request)) {
            abort(403, 'Invalid Signature');
        }

        // 3. Parse Standardized WebhookResult
        $result = $gatewayDriver->parseWebhook($request);

        // 4. Locate Transaction
        // We use reference_id which is unique per gateway.
        $transaction = Transaction::where('gateway', $driver)
            ->where('reference_id', $result->referenceId)
            ->first();

        if (! $transaction) {
            // Log warning or standard response?
            // "Webhook errors → ignored safely"
            return response()->json(['message' => 'Transaction not found'], 200); 
        }

        // 5. Enforce Idempotency
        // If the status is the same, we ignore.
        if ($transaction->status === $result->status) {
            return response()->json(['message' => 'Idempotent'], 200);
        }

        // 6. Guard State Transition
        try {
            // Update status using recorder which guards transitions.
            $this->recorder->updateStatus($transaction, $result->status);
        } catch (Exception $e) {
             // Transition not allowed (e.g. final -> final).
             // Log error.
             return response()->json(['message' => 'Invalid State Transition'], 200);
        }

        // 7. Persist Change (Done by recorder)

        // 8. Dispatch Event
        $this->dispatchEvents($transaction);

        return response()->json(['message' => 'OK'], 200);
    }

    protected function resolveDriver(string $driver): GatewayInterface
    {
        // Simple resolution logic or use container binding.
        // Assuming binding: 'indopay.driver.midtrans' or similar.
        // Or mapping.
        $class = match ($driver) {
            'midtrans' => \IndoPay\Drivers\MidtransDriver::class,
            'xendit' => \IndoPay\Drivers\XenditDriver::class,
            default => null,
        };

        if (! $class) {
            abort(404, 'Driver not found');
        }

        return app($class);
    }

    protected function dispatchEvents(Transaction $transaction): void
    {
        // Clause 10.1: Events
        match ($transaction->status) {
            PaymentStatus::PAID => event(new PaymentSucceeded($transaction)),
            PaymentStatus::FAILED => event(new PaymentFailed($transaction)),
            default => null, // Pending or others might not need new events here implies webhook
        };
    }
}

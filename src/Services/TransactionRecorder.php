<?php

namespace IndoPay\Services;

use IndoPay\Enums\PaymentStatus;
use IndoPay\Models\Transaction;
use Exception;

class TransactionRecorder
{
    /**
     * Create a new transaction.
     *
     * @param  string  $billableType
     * @param  string|int  $billableId
     * @param  string  $gateway
     * @param  int  $amount
     * @param  array  $payload
     * @return Transaction
     */
    public function create(
        string $billableType,
        mixed $billableId,
        string $gateway,
        int $amount,
        array $payload
    ): Transaction {
        // Reference ID generation should happen here or be passed in.
        // For now, let's assume specific logic or UUID.
        // Clause 7.2: reference_id is unique per gateway.
        $referenceId = uniqid($gateway . '_');

        return Transaction::create([
            'billable_type' => $billableType,
            'billable_id' => $billableId,
            'gateway' => $gateway,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'status' => PaymentStatus::PENDING,
            'payload' => $payload,
        ]);
    }

    /**
     * Update transaction status enforcing monotonic transitions.
     *
     * @param  Transaction  $transaction
     * @param  PaymentStatus  $newStatus
     * @return Transaction
     * @throws Exception
     */
    public function updateStatus(Transaction $transaction, PaymentStatus $newStatus): Transaction
    {
        // Clause 7.2 and 171: status is monotonic, final states cannot be modified.
        if ($transaction->status->isFinal()) {
             // If already final, we cannot change it unless it's a specific allowed transition (none in v1).
             // However, idempotency might mean we receive the SAME status again, which is fine.
             if ($transaction->status === $newStatus) {
                 return $transaction;
             }
             
             // If trying to change from one final state to another (e.g. PAID to FAILED), strict monotonic rules forbid it?
             // Brief says "Final state is irreversible".
             throw new Exception("Cannot transition from final state {$transaction->status->value} to {$newStatus->value}");
        }

        $transaction->update(['status' => $newStatus]);
        
        if ($newStatus === PaymentStatus::PAID) {
            $transaction->update(['paid_at' => now()]);
        }

        return $transaction;
    }
}

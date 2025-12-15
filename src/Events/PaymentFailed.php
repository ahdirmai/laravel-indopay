<?php

namespace IndoPay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IndoPay\Models\Transaction;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}
}

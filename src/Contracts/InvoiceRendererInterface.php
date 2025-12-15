<?php

namespace IndoPay\Contracts;

use IndoPay\Models\Transaction;
use Illuminate\Http\Response;

interface InvoiceRendererInterface
{
    /**
     * Render the invoice for a given transaction.
     *
     * @param  Transaction  $transaction
     * @return Response
     */
    public function render(Transaction $transaction): Response;
}

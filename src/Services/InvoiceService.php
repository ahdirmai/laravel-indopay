<?php

namespace IndoPay\Services;

use IndoPay\Contracts\InvoiceRendererInterface;
use IndoPay\Models\Transaction;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService implements InvoiceRendererInterface
{
    public function render(Transaction $transaction): Response
    {
        // 11.2 Default Implementation: Blade + DomPDF
        $pdf = Pdf::loadView('indopay::invoice', ['transaction' => $transaction]);
        
        return $pdf->download('invoice-'.$transaction->reference_id.'.pdf');
    }

    public function download(string $transactionId)
    {
        $transaction = Transaction::where('reference_id', $transactionId)->firstOrFail();
        return $this->render($transaction);
    }
}

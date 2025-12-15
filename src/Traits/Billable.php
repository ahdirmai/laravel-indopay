<?php

namespace IndoPay\Traits;

use IndoPay\Contracts\GatewayInterface;
use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\Services\PaymentService;
use Money\Money;
use Exception;

trait Billable
{
    /**
     * Charge the user.
     *
     * @param  Money  $amount
     * @param  ChargePayload  $payload
     * @return ChargeResult
     */
    public function charge(Money $amount, ChargePayload $payload): ChargeResult
    {
        // Use default gateway from config if not specified
        $defaultGateway = config('indopay.default_gateway');
        return $this->via($defaultGateway)->charge($amount, $payload);
    }

    /**
     * Specify the gateway to use.
     *
     * @param  string  $gateway
     * @return PendingCharge
     */
    public function via(string $gateway): PendingCharge
    {
        return new PendingCharge($this, $gateway);
    }

    /**
     * Download transaction invoice.
     *
     * @param  string  $transactionId
     * @return mixed
     */
    public function downloadInvoice(string $transactionId)
    {
        // Relay to InvoiceService (to be implemented)
        // For now, placeholder return
        return app('indopay.invoice')->download($transactionId);
    }
}

class PendingCharge
{
    public function __construct(
        protected $billable,
        protected string $gateway
    ) {}

    public function charge(Money $amount, ChargePayload $payload): ChargeResult
    {
        /** @var PaymentService $service */
        $service = app(PaymentService::class);
        
        // Resolve gateway driver instance
        // We need a way to get the driver instance from the string name.
        // I'll assume a GatewayManager or Factory exists or I resolve it manually here.
        // For now, manual resolution matching the controller logic (should be unified).
        $driverClass = match ($this->gateway) {
            'midtrans' => \IndoPay\Drivers\MidtransDriver::class,
            'xendit' => \IndoPay\Drivers\XenditDriver::class,
            default => throw new Exception("Gateway {$this->gateway} not configured."),
        };

        /** @var GatewayInterface $driver */
        $driver = app($driverClass);

        return $service->charge($driver, $this->billable, $amount, $payload);
    }
}

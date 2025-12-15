<?php

namespace IndoPay\Tests\Feature;

use IndoPay\Enums\PaymentStatus;
use IndoPay\Models\Transaction;
use IndoPay\Tests\TestCase;
use IndoPay\Events\PaymentSucceeded;
use Illuminate\Support\Facades\Event;
use Mockery;
use IndoPay\Contracts\GatewayInterface;
use IndoPay\DTO\WebhookResult;
use Money\Money;

class WebhookTest extends TestCase
{
    public function test_webhook_updates_status_and_dispatches_event()
    {
        Event::fake();

        // Create initial transaction
        $transaction = Transaction::create([
            'billable_type' => 'User',
            'billable_id' => 1,
            'gateway' => 'midtrans',
            'reference_id' => 'order-123',
            'amount' => 10000,
            'status' => PaymentStatus::PENDING,
        ]);

        // Mock the driver resolution in the container
        // Since WebhookController creates new instance or resolves, we need to bind a mock.
        // The controller uses `app($class)`.
        $mockDriver = Mockery::mock(\IndoPay\Drivers\MidtransDriver::class);
        $mockDriver->shouldReceive('verifySignature')->andReturn(true);
        $mockDriver->shouldReceive('parseWebhook')->andReturn(new WebhookResult(
            referenceId: 'order-123',
            gatewayReferenceId: 'g-123',
            status: PaymentStatus::PAID,
            amount: Money::IDR(10000),
            rawPayload: []
        ));

        $this->app->instance(\IndoPay\Drivers\MidtransDriver::class, $mockDriver);

        $response = $this->postJson('/indopay/webhook/midtrans', [
            'order_id' => 'order-123',
            'transaction_status' => 'settlement'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);

        Event::assertDispatched(PaymentSucceeded::class);
    }
    
    public function test_webhook_idempotency()
    {
        Event::fake();
        
        $transaction = Transaction::create([
            'billable_type' => 'User',
            'billable_id' => 1,
            'gateway' => 'midtrans',
            'reference_id' => 'order-123',
            'amount' => 10000,
            'status' => PaymentStatus::PAID, // Already paid
        ]);
        
        $mockDriver = Mockery::mock(\IndoPay\Drivers\MidtransDriver::class);
        $mockDriver->shouldReceive('verifySignature')->andReturn(true);
        $mockDriver->shouldReceive('parseWebhook')->andReturn(new WebhookResult(
            referenceId: 'order-123',
            gatewayReferenceId: 'g-123',
            status: PaymentStatus::PAID,
            amount: Money::IDR(10000),
            rawPayload: []
        ));
        
        $this->app->instance(\IndoPay\Drivers\MidtransDriver::class, $mockDriver);
        
        $response = $this->postJson('/indopay/webhook/midtrans', []); // Payload doesn't matter as mock returns result
        
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Idempotent']);
        
        // Event should NOT be dispatched again? 
        // Brief 10.2: "Events are never fired on replay".
        // Dispatch logic in controller is after idempotency check.
        // So it should not dispatch.
        Event::assertNotDispatched(PaymentSucceeded::class);
    }
}

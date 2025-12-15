<?php

namespace IndoPay\Tests\Unit;

use IndoPay\DTO\ChargePayload;
use IndoPay\DTO\ChargeResult;
use IndoPay\Enums\PaymentStatus;
use IndoPay\Services\PaymentService;
use IndoPay\Services\TransactionRecorder;
use IndoPay\Tests\TestCase;
use IndoPay\Contracts\GatewayInterface;
use Mockery;
use Money\Money;
use IndoPay\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class PaymentServiceTest extends TestCase
{
    public function test_charge_creates_transaction_and_calls_gateway()
    {
        // Mock Gateway
        $gateway = Mockery::mock(GatewayInterface::class);
        if (method_exists($gateway, 'getName')) {
            $gateway->shouldReceive('getName')->andReturn('mock_gateway');
        } else {
             // If interface doesn't have it, we rely on implementation details or test setup
        }
        
        $gateway->shouldReceive('charge')
            ->once()
            ->andReturn(new ChargeResult('ref-123', PaymentStatus::PENDING));

        // Mock Billable
        $user = new class extends Model {
            protected $table = 'users';
            protected $guarded = [];
        };
        $user->id = 1;
        $user->exists = true;

        $service = app(PaymentService::class);
        $payload = new ChargePayload(['email' => 'test@example.com']);
        $amount = Money::IDR(10000);

        $result = $service->charge($gateway, $user, $amount, $payload);

        $this->assertInstanceOf(ChargeResult::class, $result);
        $this->assertEquals('ref-123', $result->referenceId);

        $this->assertDatabaseHas('payment_transactions', [
            'billable_id' => 1,
            'amount' => 10000,
            'status' => 'pending',
        ]);
    }
}

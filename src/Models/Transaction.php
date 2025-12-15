<?php

namespace IndoPay\Models;

use Illuminate\Database\Eloquent\Model;
use IndoPay\Enums\PaymentStatus;

class Transaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'billable_type',
        'billable_id',
        'gateway',
        'reference_id',
        'amount',
        'status',
        'payload',
        'paid_at',
        'gateway_event_id',
        'last_webhook_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'payload' => 'array',
        'paid_at' => 'datetime',
        'last_webhook_at' => 'datetime',
        'amount' => 'integer',
    ];

    /**
     * The attributes that should not be updated after creation.
     */
    protected $guarded_immutable = [
        'gateway',
    ];

    // TODO: Implement immutability checks in booting method or Observer
}

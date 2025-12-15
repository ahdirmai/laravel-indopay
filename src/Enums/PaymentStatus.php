<?php

namespace IndoPay\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';

    /**
     * Determine if the status is a final state.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::FAILED, self::EXPIRED, self::REFUNDED]);
    }
}

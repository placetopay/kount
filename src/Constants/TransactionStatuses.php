<?php

namespace PlacetoPay\Kount\Constants;

class TransactionStatuses
{
    public const PENDING = 'PENDING';
    public const AUTHORIZED = 'AUTHORIZED';
    public const REFUSED = 'REFUSED';
    public const CAPTURED = 'CAPTURED';
    public const ERROR = 'ERROR';
    public const EXPIRED = 'EXPIRED';
    public const CANCELED = 'CANCELED';
    public const SENT_FOR_REFUND = 'SENT_FOR_REFUND';
    public const REFUNDED = 'REFUNDED';
    public const REFUND_FAILED = 'REFUND_FAILED';
    public const SETTLED = 'SETTLED';
    public const INFORMATION_REQUESTED = 'INFORMATION_REQUESTED';
    public const INFORMATION_SUPPLIED = 'INFORMATION_SUPPLIED';
    public const CHARGED_BACK = 'CHARGED_BACK';
    public const CHARGEBACK_REVERSED = 'CHARGEBACK_REVERSED';
    public const DISPUTE_EXPIRED = 'DISPUTE_EXPIRED';
    public const DISPUTE_RESERVE_RELEASED = 'DISPUTE_RESERVE_RELEASED';
    public const DISPUTED_FUNDS_HELD = 'DISPUTED_FUNDS_HELD';
    public const DISPUTED_FUNDS_RELEASED = 'DISPUTED_FUNDS_RELEASED';
}

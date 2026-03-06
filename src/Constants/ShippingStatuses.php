<?php

namespace PlacetoPay\Kount\Constants;

class ShippingStatuses
{
    public const PENDING = 'PENDING';
    public const UNFULFILLED = 'UNFULFILLED';
    public const ON_HOLD = 'ON_HOLD';
    public const FULFILLED = 'FULFILLED';
    public const SCHEDULED = 'SCHEDULED';
    public const PARTIALLY_FULFILLED = 'PARTIALLY_FULFILLED';
    public const DELAYED = 'DELAYED';
    public const CANCELED = 'CANCELED';

    public static function toArray(): array
    {
        return [
            self::PENDING,
            self::UNFULFILLED,
            self::ON_HOLD,
            self::FULFILLED,
            self::SCHEDULED,
            self::PARTIALLY_FULFILLED,
            self::DELAYED,
            self::CANCELED,
        ];
    }
}

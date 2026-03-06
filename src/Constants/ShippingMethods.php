<?php

namespace PlacetoPay\Kount\Constants;

class ShippingMethods
{
    public const STANDARD = 'STANDARD';
    public const EXPRESS = 'EXPRESS';
    public const SAME_DAY = 'SAME_DAY';
    public const NEXT_DAY = 'NEXT_DAY';
    public const SECOND_DAY = 'SECOND_DAY';

    public static function toArray(): array
    {
        return [
            self::STANDARD,
            self::EXPRESS,
            self::SAME_DAY,
            self::NEXT_DAY,
            self::SECOND_DAY,
        ];
    }
}

<?php

namespace PlacetoPay\Kount\Constants;

class ShippingTypes
{
    public const SHIPPED = 'SHIPPED';
    public const DIGITAL = 'DIGITAL';
    public const STORE_PICK_UP = 'STORE_PICK_UP';
    public const LOCAL_DELIVERY = 'LOCAL_DELIVERY';
    public const STORE_DRIVE_UP = 'STORE_DRIVE_UP';
    public const IN_PERSON = 'IN_PERSON';

    public static function toArray(): array
    {
        return [
            self::SHIPPED,
            self::DIGITAL,
            self::STORE_PICK_UP,
            self::LOCAL_DELIVERY,
            self::STORE_DRIVE_UP,
        ];
    }
}

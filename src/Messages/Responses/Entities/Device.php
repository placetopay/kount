<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Helpers\ArrayHelper;

class Device
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function wasVerifiedUsingDevice(): bool
    {
        return !is_null(ArrayHelper::get($this->data, 'id'));
    }
}

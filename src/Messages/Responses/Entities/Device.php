<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Traits\HasData;

class Device
{
    use HasData;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function wasVerifiedUsingDevice(): bool
    {
        return !is_null($this->get('id'));
    }
}

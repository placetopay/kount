<?php

namespace PlacetoPay\Kount\Traits;

use PlacetoPay\Kount\Helpers\ArrayHelper;

trait HasData
{
    protected array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return ArrayHelper::get($this->data, $key, $default);
    }
}

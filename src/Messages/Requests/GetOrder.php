<?php

namespace PlacetoPay\Kount\Messages\Requests;

class GetOrder extends Base
{
    public function method(): string
    {
        return 'GET';
    }

    public function body(): array
    {
        return [];
    }

    public function url(): string
    {
        return parent::url() . '/' . $this->data['orderId'];
    }
}

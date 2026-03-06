<?php

namespace PlacetoPay\Kount\Messages\Responses;

class CreateOrder extends Base
{
    public function orderId(): ?string
    {
        return $this->get('order.orderId');
    }
}

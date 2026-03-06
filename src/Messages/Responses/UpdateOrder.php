<?php

namespace PlacetoPay\Kount\Messages\Responses;

class UpdateOrder extends Base
{
    public function orderId(): ?string
    {
        return $this->get('orderId');
    }
}

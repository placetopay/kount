<?php

namespace PlacetoPay\Kount\Messages\Responses;

use GuzzleHttp\Psr7\Response;
use PlacetoPay\Kount\Messages\Responses\Entities\Order;

class InquiryOrder extends CreateOrder
{
    private ?Order $order = null;

    public function __construct(Response $response)
    {
        parent::__construct($response);

        if ($order = $this->get('order')) {
            $this->order = new Order($order);
        }
    }

    public function order(): ?Order
    {
        return $this->order;
    }
}

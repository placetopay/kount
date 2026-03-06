<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Traits\HasData;

class Order
{
    use HasData;

    private ?RiskInquiry $riskInquiry = null;

    public function __construct(array $data)
    {
        $this->data = $data;

        if ($riskInquiry = $this->get('riskInquiry')) {
            $this->riskInquiry = new RiskInquiry($riskInquiry);
        }
    }

    public function riskInquiry(): ?RiskInquiry
    {
        return $this->riskInquiry;
    }
}

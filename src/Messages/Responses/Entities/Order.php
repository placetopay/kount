<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Helpers\ArrayHelper;

class Order
{
    private ?RiskInquiry $riskInquiry = null;

    public function __construct(array $data)
    {
        if ($riskInquiry = ArrayHelper::get($data, 'riskInquiry')) {
            $this->riskInquiry = new RiskInquiry($riskInquiry);
        }
    }

    public function riskInquiry(): ?RiskInquiry
    {
        return $this->riskInquiry;
    }
}

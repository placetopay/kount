<?php

namespace PlacetoPay\Kount\Messages\Requests;

class InquiryOrder extends CreateOrder
{
    public function url(): string
    {
        return parent::url() . '?riskInquiry=true';
    }
}

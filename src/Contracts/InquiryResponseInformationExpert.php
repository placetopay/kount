<?php

namespace PlacetoPay\Kount\Contracts;

use PlacetoPay\Kount\Messages\InquiryResponse;

abstract class InquiryResponseInformationExpert
{
    public function __construct(private InquiryResponse $parent)
    {
    }

    protected function data($key = null, $default = null): ?string
    {
        return $this->parent->data($key, $default);
    }

    abstract public function toArray(): array;
}

<?php

namespace PlacetoPay\Kount\Contracts;

use PlacetoPay\Kount\Messages\InquiryResponse;

abstract class InquiryResponseInformationExpert
{
    private InquiryResponse $parent;

    public function __construct(InquiryResponse $parent)
    {
        $this->parent = $parent;
    }

    protected function data($key = null, $default = null): ?string
    {
        return $this->parent->data($key, $default);
    }

    abstract public function toArray(): array;
}

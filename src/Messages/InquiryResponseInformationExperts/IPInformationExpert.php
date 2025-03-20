<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class IPInformationExpert extends InquiryResponseInformationExpert
{
    public function address(): ?string
    {
        return $this->data('IP_IPAD');
    }

    public function latitude(): ?string
    {
        return $this->data('IP_LAT');
    }

    public function longitude(): ?string
    {
        return $this->data('IP_LON');
    }

    public function country(): ?string
    {
        return $this->data('IP_COUNTRY');
    }

    public function state(): ?string
    {
        return $this->data('IP_REGION');
    }

    public function city(): ?string
    {
        return $this->data('IP_CITY');
    }

    public function provider(): ?string
    {
        return $this->data('IP_ORG');
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address(),
            'latitude' => $this->latitude(),
            'longitude' => $this->longitude(),
            'country' => $this->country(),
            'state' => $this->state(),
            'city' => $this->city(),
            'provider' => $this->provider(),
        ];
    }
}

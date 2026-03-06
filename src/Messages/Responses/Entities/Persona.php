<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Traits\HasData;

class Persona
{
    use HasData;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function riskiestCountry(): ?string
    {
        return $this->get('riskiestCountry');
    }

    public function totalBankApprovedOrders(): ?int
    {
        return $this->get('totalBankApprovedOrders');
    }

    public function maxVelocity(): ?int
    {
        return $this->get('maxVelocity');
    }

    public function uniqueCards(): ?int
    {
        return $this->get('uniqueCards');
    }

    public function uniqueEmails(): ?int
    {
        return $this->get('uniqueEmails');
    }

    public function uniqueDevices(): ?int
    {
        return $this->get('uniqueDevices');
    }
}

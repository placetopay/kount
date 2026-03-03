<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Helpers\ArrayHelper;

class Persona
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function riskiestCountry(): ?string
    {
        return ArrayHelper::get($this->data, 'riskiestCountry');
    }

    public function totalBankApprovedOrders(): ?int
    {
        return ArrayHelper::get($this->data, 'totalBankApprovedOrders');
    }

    public function maxVelocity(): ?int
    {
        return ArrayHelper::get($this->data, 'maxVelocity');
    }

    public function uniqueCards(): ?int
    {
        return ArrayHelper::get($this->data, 'uniqueCards');
    }

    public function uniqueEmails(): ?int
    {
        return ArrayHelper::get($this->data, 'uniqueEmails');
    }

    public function uniqueDevices(): ?int
    {
        return ArrayHelper::get($this->data, 'uniqueDevices');
    }
}

<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Constants\DecisionCodes;
use PlacetoPay\Kount\Traits\HasData;

class RiskInquiry
{
    use HasData;

    private ?Device $device = null;
    private ?Persona $persona = null;

    public function __construct(array $data)
    {
        $this->data = $data;

        if ($device = $this->get('device')) {
            $this->device = new Device($device);
        }

        if ($persona = $this->get('persona')) {
            $this->persona = new Persona($persona);
        }
    }

    public function device(): ?Device
    {
        return $this->device;
    }

    public function persona(): ?Persona
    {
        return $this->persona;
    }

    public function omniscore(): ?float
    {
        return $this->get('omniscore');
    }

    public function decision(): ?string
    {
        return $this->get('decision');
    }

    public function shouldApprove(): bool
    {
        return $this->get('decision') === DecisionCodes::APPROVE;
    }

    public function shouldDecline(): bool
    {
        return $this->get('decision') === DecisionCodes::DECLINE;
    }

    public function shouldReview(): bool
    {
        return $this->get('decision') === DecisionCodes::REVIEW;
    }

    public function rulesTriggered(): array
    {
        return $this->get('segmentExecuted.policiesExecuted', []);
    }
}

<?php

namespace PlacetoPay\Kount\Messages\Responses\Entities;

use PlacetoPay\Kount\Constants\DecisionCodes;
use PlacetoPay\Kount\Helpers\ArrayHelper;

class RiskInquiry
{
    private ?Device $device = null;
    private ?Persona $persona = null;
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        if ($device = ArrayHelper::get($data, 'device')) {
            $this->device = new Device($device);
        }

        if ($persona = ArrayHelper::get($data, 'persona')) {
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
        return ArrayHelper::get($this->data, 'omniscore');
    }

    public function decision(): ?string
    {
        return ArrayHelper::get($this->data, 'decision');
    }

    public function shouldApprove(): bool
    {
        return ArrayHelper::get($this->data, 'decision') === DecisionCodes::APPROVE;
    }

    public function shouldDecline(): bool
    {
        return ArrayHelper::get($this->data, 'decision') === DecisionCodes::DECLINE;
    }

    public function shouldReview(): bool
    {
        return ArrayHelper::get($this->data, 'decision') === DecisionCodes::REVIEW;
    }

    public function rulesTriggered(): array
    {
        return ArrayHelper::get($this->data, 'segmentExecuted.policiesExecuted', []);
    }
}

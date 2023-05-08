<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Constants\DecisionCodes;
use PlacetoPay\Kount\Constants\DecisionReasons;
use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class DecisionInformationExpert extends InquiryResponseInformationExpert
{
    public function code(): ?string
    {
        return $this->data('AUTO');
    }

    public function description(): ?string
    {
        return DecisionReasons::REASONS[$this->code()] ?: DecisionReasons::ERROR;
    }

    public function shouldApprove(): bool
    {
        return $this->code() === DecisionCodes::APPROVE;
    }

    public function shouldDecline(): bool
    {
        return $this->code() === DecisionCodes::DECLINE;
    }

    public function shouldReview(): bool
    {
        return $this->code() === DecisionCodes::REVIEW;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code(),
            'description' => $this->description(),
            'shouldApprove' => $this->shouldApprove(),
            'shouldDecline' => $this->shouldDecline(),
            'shouldReview' => $this->shouldReview(),
        ];
    }
}

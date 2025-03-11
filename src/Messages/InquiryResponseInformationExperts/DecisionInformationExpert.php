<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Constants\Decisions;
use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class DecisionInformationExpert extends InquiryResponseInformationExpert
{
    public function code(): ?string
    {
        return $this->data('AUTO');
    }

    public function description(): ?string
    {
        return Decisions::REASONS[$this->code()] ?: Decisions::ERROR_REASON;
    }

    public function shouldApprove(): bool
    {
        return $this->code() === Decisions::APPROVE;
    }

    public function shouldDecline(): bool
    {
        return $this->code() === Decisions::DECLINE;
    }

    public function shouldReview(): bool
    {
        return $this->code() === Decisions::REVIEW;
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

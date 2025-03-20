<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class AdditionalInformationExpert extends InquiryResponseInformationExpert
{
    public function dateSinceFirstMadeTransaction(): ?string
    {
        return $this->data('DDFS');
    }

    public function screenResolution(): string
    {
        return $this->data('DSR');
    }

    public function userAgent(): string
    {
        return $this->data('UAS');
    }

    public function operativeSystem(): string
    {
        return $this->data('OS');
    }

    public function browser(): ?string
    {
        return $this->data('BROWSER');
    }

    public function wasPreviouslyWhitelisted(): bool
    {
        return $this->data('PREVIOUSLY_WHITELISTED') === 'Y';
    }

    public function toArray(): array
    {
        return [
            'dateSinceFirstMadeTransaction' => $this->dateSinceFirstMadeTransaction(),
            'screenResolution' => $this->screenResolution(),
            'userAgent' => $this->userAgent(),
            'operativeSystem' => $this->operativeSystem(),
            'browser' => $this->browser(),
            'wasPreviouslyWhitelisted' => $this->wasPreviouslyWhitelisted(),
        ];
    }
}

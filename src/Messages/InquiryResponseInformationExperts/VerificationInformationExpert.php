<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class VerificationInformationExpert extends InquiryResponseInformationExpert
{
    public function geolocationCountry(): ?string
    {
        return $this->data('GEOX');
    }

    public function geolocationRegion(): ?string
    {
        return $this->data('REGN');
    }

    public function cardBrand(): ?string
    {
        return $this->data('BRND');
    }

    public function cardIsBlacklisted(): bool
    {
        return $this->data('NETW') === 'Y';
    }

    public function aCatchVerificationHasBeenPerformed(): bool
    {
        return $this->data('KAPT') === 'Y';
    }

    public function threeDsMerchantResponse(): ?string
    {
        return $this->data('THREE_DS_MERCHANT_RESPONSE');
    }

    public function denialReasonCode(): ?string
    {
        return $this->data('REASON_CODE');
    }

    public function toArray(): array
    {
        return [
            'geolocationCountry' => $this->geolocationCountry(),
            'geolocationRegion' => $this->geolocationRegion(),
            'cardBrand' => $this->cardBrand(),
            'cardIsBlacklisted' => $this->cardIsBlacklisted(),
            'aCatchVerificationHasBeenPerformed' => $this->aCatchVerificationHasBeenPerformed(),
            'threeDsMerchantResponse' => $this->threeDsMerchantResponse(),
            'denialReasonCode' => $this->denialReasonCode(),
        ];
    }
}

<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class TransactionInformationExpert extends InquiryResponseInformationExpert
{
    public function id(): ?string
    {
        return $this->data('TRAN');
    }

    public function usedCardsCount(): int
    {
        return (int)$this->data('CARDS');
    }

    public function usedDevicesCount(): int
    {
        return (int)$this->data('DEVICES');
    }

    public function deviceLayers(): ?string
    {
        return $this->data('DEVICE_LAYERS');
    }

    public function usedEmailsCount(): int
    {
        return (int)$this->data('EMAILS');
    }

    public function velocity(): int
    {
        return (int)$this->data('VELO');
    }

    public function maxAllowedVelocity(): int
    {
        return (int)$this->data('VMAX');
    }

    public function site(): ?string
    {
        return $this->data('SITE');
    }

    public function fingerprint(): ?string
    {
        return $this->data('FINGERPRINT');
    }

    public function timezone(): ?string
    {
        return $this->data('TIMEZONE');
    }

    public function localtime(): ?string
    {
        return $this->data('LOCALTIME');
    }

    public function region(): ?string
    {
        return $this->data('REGION');
    }

    public function country(): ?string
    {
        return $this->data('COUNTRY');
    }

    public function httpCountry(): ?string
    {
        return $this->data('HTTP_COUNTRY');
    }

    public function hasProxy(): bool
    {
        return $this->data('PROXY') === 'Y';
    }

    public function hasJavascript(): bool
    {
        return $this->data('JAVASCRIPT') === 'Y';
    }

    public function hasFlash(): bool
    {
        return $this->data('FLASH') === 'Y';
    }

    public function hasCookies(): bool
    {
        return $this->data('COOKIES') === 'Y';
    }

    public function language(): ?string
    {
        return strtolower($this->data('LANGUAGE')) ?: null;
    }

    public function processedFromMobileDevice(): bool
    {
        return $this->data('MOBILE_DEVICE') === 'Y';
    }

    public function mobileType(): ?string
    {
        return $this->data('MOBILE_TYPE');
    }

    public function mobileIsThroughMobileForwarder(): bool
    {
        return $this->data('MOBILE_FORWARDER') === 'Y';
    }

    public function processedFromVoiceDevice(): bool
    {
        return $this->data('VOICE_DEVICE') === 'Y';
    }

    public function processedFromRemotePC(): bool
    {
        return $this->data('PC_REMOTE') === 'Y';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'usedCardsCount' => $this->usedCardsCount(),
            'usedDevicesCount' => $this->usedDevicesCount(),
            'deviceLayers' => $this->deviceLayers(),
            'usedEmailsCount' => $this->usedEmailsCount(),
            'velocity' => $this->velocity(),
            'maxAllowedVelocity' => $this->maxAllowedVelocity(),
            'site' => $this->site(),
            'fingerprint' => $this->fingerprint(),
            'timezone' => $this->timezone(),
            'localtime' => $this->localtime(),
            'region' => $this->region(),
            'country' => $this->country(),
            'httpCountry' => $this->httpCountry(),
            'hasProxy' => $this->hasProxy(),
            'hasJavascript' => $this->hasJavascript(),
            'hasFlash' => $this->hasFlash(),
            'hasCookies' => $this->hasCookies(),
            'language' => $this->language(),
            'processedFromMobileDevice' => $this->processedFromMobileDevice(),
            'mobileType' => $this->mobileType(),
            'mobileIsThroughMobileForwarder' => $this->mobileIsThroughMobileForwarder(),
            'processedFromVoiceDevice' => $this->processedFromVoiceDevice(),
            'processedFromRemotePC' => $this->processedFromRemotePC(),
        ];
    }
}

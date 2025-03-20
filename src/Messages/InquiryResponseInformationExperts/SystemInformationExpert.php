<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class SystemInformationExpert extends InquiryResponseInformationExpert
{
    public function version(): ?string
    {
        return $this->data('VERS');
    }

    public function mode(): ?string
    {
        return $this->data('MODE');
    }

    public function merchantId(): ?string
    {
        return $this->data('MERC');
    }

    public function sessionId(): ?string
    {
        return $this->data('SESS');
    }

    public function orderReference(): ?string
    {
        return $this->data('ORDR');
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version(),
            'mode' => $this->mode(),
            'merchantId' => $this->merchantId(),
            'sessonId' => $this->sessionId(),
            'orderReference' => $this->orderReference(),
        ];
    }
}

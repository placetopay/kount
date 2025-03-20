<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class WarningsInformationExpert extends InquiryResponseInformationExpert
{
    public function count(): int
    {
        return (int)$this->data('WARNING_COUNT');
    }

    public function warnings(): array
    {
        if ($this->count() === 0) {
            return [];
        }

        $warnings = [];

        for ($i = 0; $i < $this->count(); $i++) {
            $warnings[] = $this->data("WARNING_$i");
        }

        return $warnings;
    }

    public function toArray(): array
    {
        return $this->warnings();
    }
}

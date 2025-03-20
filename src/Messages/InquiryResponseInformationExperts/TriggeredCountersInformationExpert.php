<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class TriggeredCountersInformationExpert extends InquiryResponseInformationExpert
{
    public function count(): int
    {
        return (int)$this->data('COUNTERS_TRIGGERED');
    }

    public function counters(): array
    {
        if ($this->count() === 0) {
            return [];
        }

        $counters = [];

        for ($i = 0; $i < $this->count(); $i++) {
            $counters[$this->data("COUNTER_NAME_$i")] = $this->data("COUNTER_VALUE_$i");
        }

        return $counters;
    }

    public function toArray(): array
    {
        return $this->counters();
    }
}

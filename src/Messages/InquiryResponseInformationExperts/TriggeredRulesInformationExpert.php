<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;

class TriggeredRulesInformationExpert extends InquiryResponseInformationExpert
{
    public function count(): int
    {
        return (int)$this->data('RULES_TRIGGERED');
    }

    public function rules(): array
    {
        if ($this->count() === 0) {
            return [];
        }

        $rules = [];

        for ($i = 0; $i < $this->count(); $i++) {
            $rules[$this->data("RULE_ID_$i")] = $this->data("RULE_DESCRIPTION_$i");
        }

        return $rules;
    }

    public function toArray(): array
    {
        return $this->rules();
    }
}

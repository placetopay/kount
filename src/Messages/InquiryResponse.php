<?php


namespace PlacetoPay\Kount\Messages;


class InquiryResponse extends Response
{
    protected $rules = [];

    public function rulesTriggered()
    {
        if (!$this->rules) {
            $i = 0;
            while ($rule = $this->data('RULE_ID_' .$i)) {
                $this->rules[$rule] = $this->data('RULE_DESCRIPTION_' . $i);
                $i++;
            }
        }
        return $this->rules;
    }

}
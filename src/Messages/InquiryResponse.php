<?php


namespace PlacetoPay\Kount\Messages;


class InquiryResponse extends Response
{
    protected $rules = [];

    public function rulesTriggered()
    {
        if (!$this->rules) {
            $i = 0;
            while ($rule = $this->data('RULE_ID_' . $i)) {
                $this->rules[$rule] = $this->data('RULE_DESCRIPTION_' . $i);
                $i++;
            }
        }
        return $this->rules;
    }

    public function kountCode()
    {
        return $this->data('TRAN');
    }

    public function score()
    {
        return $this->data('SCOR');
    }

    // Decision based

    public function shouldApprove()
    {
        return $this->data('AUTO') == 'A';
    }

    public function shouldDecline()
    {
        return $this->data('AUTO') == 'D';
    }

    public function shouldReview()
    {
        return $this->data('AUTO') == 'R';
    }

    public function decision()
    {
        return $this->data('AUTO');
    }

    public function deviceLayers()
    {
        return $this->data('DEVICE_LAYERS');
    }

    public function userAgent()
    {
        return $this->data('UAS');
    }

    public function operativeSystem()
    {
        return $this->data('OS');
    }

    public function screenResolution()
    {
        return $this->data('DSR');
    }

    public function ipAddress()
    {
        return $this->data('IP_IPAD');
    }

    public function ipLatitude()
    {
        return $this->data('IP_LAT');
    }

    public function ipLongitude()
    {
        return $this->data('IP_LON');
    }

    public function ipCountry()
    {
        return $this->data('IP_COUNTRY');
    }

    public function ipState()
    {
        return $this->data('IP_REGION');
    }

    public function ipCity()
    {
        return $this->data('IP_CITY');
    }

    public function ipProvider()
    {
        return $this->data('IP_ORG');
    }

    public function fingerprint()
    {
        return $this->data('FINGERPRINT');
    }

    public function language()
    {
        return $this->data('LANGUAGE') ? strtolower($this->data('LANGUAGE')) : null;
    }

    public function hasProxy()
    {
        return $this->data('PROXY') == 'Y';
    }

    public function hasJavascript()
    {
        return $this->data('JAVASCRIPT') == 'Y';
    }

    public function hasFlash()
    {
        return $this->data('FLASH') == 'Y';
    }

    public function hasCookies()
    {
        return $this->data('COOKIES') == 'Y';
    }
}
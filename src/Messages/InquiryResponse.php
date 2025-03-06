<?php

namespace PlacetoPay\Kount\Messages;

use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\AdditionalInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\DecisionInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\ErrorsInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\IPInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\SystemInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\TransactionInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\TriggeredCountersInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\TriggeredRulesInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\VerificationInformationExpert;
use PlacetoPay\Kount\Messages\InquiryResponseInformationExperts\WarningsInformationExpert;

class InquiryResponse extends Response
{
    public SystemInformationExpert $system;
    public DecisionInformationExpert $decision;
    public VerificationInformationExpert $verification;
    public TransactionInformationExpert $transaction;
    public IPInformationExpert $ip;
    public AdditionalInformationExpert $additional;
    public TriggeredRulesInformationExpert $triggeredRules;
    public TriggeredCountersInformationExpert $triggeredCounters;
    public WarningsInformationExpert $warnings;
    public ErrorsInformationExpert $errors;

    /**
     * @throws KountServiceException
     */
    public function __construct(string $response)
    {
        parent::__construct($response);

        $this->system = new SystemInformationExpert($this);
        $this->decision = new DecisionInformationExpert($this);
        $this->verification = new VerificationInformationExpert($this);
        $this->transaction = new TransactionInformationExpert($this);
        $this->ip = new IPInformationExpert($this);
        $this->additional = new AdditionalInformationExpert($this);
        $this->triggeredRules = new TriggeredRulesInformationExpert($this);
        $this->triggeredCounters = new TriggeredCountersInformationExpert($this);
        $this->warnings = new WarningsInformationExpert($this);
        $this->errors = new ErrorsInformationExpert($this);
    }

    public function rulesTriggered()
    {
        return $this->triggeredRules->rules();
    }

    public function kountCode()
    {
        return $this->transaction->id();
    }

    public function score(): int
    {
        return (int)$this->data('SCOR');
    }

    public function omniscore(): int
    {
        return (int)$this->data('OMNISCORE');
    }
    public function shouldApprove(): bool
    {
        return $this->decision->shouldApprove();
    }

    public function shouldDecline(): bool
    {
        return $this->decision->shouldDecline();
    }

    public function shouldReview(): bool
    {
        return $this->decision->shouldReview();
    }

    public function decision(): ?string
    {
        return $this->decision->code();
    }

    public function deviceLayers(): ?string
    {
        return $this->transaction->deviceLayers();
    }

    public function userAgent(): string
    {
        return $this->additional->userAgent();
    }

    public function operativeSystem(): string
    {
        return $this->additional->operativeSystem();
    }

    public function screenResolution(): string
    {
        return $this->additional->screenResolution();
    }

    public function ipAddress(): ?string
    {
        return $this->ip->address();
    }

    public function ipLatitude(): ?string
    {
        return $this->ip->latitude();
    }

    public function ipLongitude(): ?string
    {
        return $this->ip->longitude();
    }

    public function ipCountry(): ?string
    {
        return $this->ip->country();
    }

    public function ipState(): ?string
    {
        return $this->ip->state();
    }

    public function ipCity(): ?string
    {
        return $this->ip->city();
    }

    public function ipProvider(): ?string
    {
        return $this->ip->provider();
    }

    public function fingerprint(): ?string
    {
        return $this->transaction->fingerprint();
    }

    public function language(): ?string
    {
        return $this->transaction->language();
    }

    public function hasProxy(): bool
    {
        return $this->transaction->hasProxy();
    }

    public function hasJavascript(): bool
    {
        return $this->transaction->hasJavascript();
    }

    public function hasFlash(): bool
    {
        return $this->transaction->hasFlash();
    }

    public function hasCookies(): bool
    {
        return $this->transaction->hasCookies();
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score(),
            'omniscore' => $this->omniscore(),
            'system' => $this->system->toArray(),
            'decision' => $this->decision->toArray(),
            'verification' => $this->verification->toArray(),
            'transaction' => $this->transaction->toArray(),
            'ip' => $this->ip->toArray(),
            'additional' => $this->additional->toArray(),
            'triggeredRules' => $this->triggeredRules->toArray(),
            'triggeredCounters' => $this->triggeredCounters->toArray(),
            'warnings' => $this->warnings->toArray(),
            'errorsBag' => $this->errors->toArray(),
        ];
    }
}

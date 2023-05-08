<?php

namespace PlacetoPay\Kount\Constants;

class DecisionReasons
{
    public const APPROVE = 'APPROVE';
    public const DECLINE = 'DECLINE';
    public const REVIEW = 'REVIEW';
    public const ERROR = 'ERROR';

    public const REASONS = [
        DecisionCodes::APPROVE => self::APPROVE,
        DecisionCodes::REVIEW => self::REVIEW,
        DecisionCodes::DECLINE => self::DECLINE,
        DecisionCodes::ERROR => self::ERROR,
    ];
}

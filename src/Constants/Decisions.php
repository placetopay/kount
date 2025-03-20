<?php

namespace PlacetoPay\Kount\Constants;

class Decisions
{
    public const APPROVE = 'A';
    public const DECLINE = 'D';
    public const REVIEW = 'R';
    public const ERROR = 'E';

    public const APPROVE_REASON = 'APPROVE';
    public const DECLINE_REASON = 'DECLINE';
    public const REVIEW_REASON = 'REVIEW';
    public const ERROR_REASON = 'ERROR';

    public const REASONS = [
        self::APPROVE => self::APPROVE_REASON,
        self::REVIEW => self::REVIEW_REASON,
        self::DECLINE => self::DECLINE_REASON,
        self::ERROR => self::ERROR_REASON,
    ];
}

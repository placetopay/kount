<?php

namespace PlacetoPay\Kount\Exceptions;

use Exception;

class KountServiceException extends Exception
{
    public static function forErrorResponse(array $errors): self
    {
        return new self(implode("\n", $errors));
    }
}

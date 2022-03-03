<?php

namespace PlacetoPay\Kount\Exceptions;

use PlacetoPay\Kount\Messages\Response;

class KountServiceException extends \Exception
{
    public static function forErrorResponse(Response $response): self
    {
        return new self(implode("\n", $response->errors()));
    }
}

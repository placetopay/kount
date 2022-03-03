<?php

namespace PlacetoPay\Kount\Exceptions;

class KountServiceException extends \Exception
{
    public static function forErrorResponse(array $data): self
    {
        $messages = [];
        for ($i = 0; $i < $data['ERROR_COUNT']; $i++) {
            $messages[] = $data['ERROR_' . $i];
        }
        return new self(implode("\n", $messages));
    }
}

<?php

namespace PlacetoPay\Kount\Messages;

use PlacetoPay\Kount\Entities\KountError;
use PlacetoPay\Kount\Exceptions\KountServiceException;

class Response
{
    protected $raw;
    protected $data;

    protected $errors = [];

    public function __construct($response)
    {
        $this->raw = $response;
        $lines = preg_split('/[\r\n]+/', $response, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);
            $this->data[$key] = $value;
        }

        if ($this->data('MODE') == 'E') {
            throw KountServiceException::forErrorResponse($this);
        }
    }

    public function raw()
    {
        return $this->raw;
    }

    public function data($key = null, $default = null)
    {
        if ($key) {
            if (isset($this->data[$key])) {
                return $this->data[$key];
            }
            return $default;
        }
        return $this->data;
    }

    // Error related

    public function isErrorResponse(): bool
    {
        return $this->data('MODE') === 'E';
    }

    public function errorCount(): int
    {
        return (int)$this->data('ERROR_COUNT');
    }

    public function errorCode()
    {
        return $this->data('ERRO');
    }

    /**
     * Returns the KEY error that can be translated into an message.
     * @return string
     */
    public function errorKey()
    {
        if ($this->errorCode()) {
            return KountError::errorKey($this->errorCode());
        }
        return null;
    }

    public function errors(): array
    {
        $messages = [];
        if ($this->isErrorResponse()) {
            if ($this->errorCount()) {
                for ($i = 0; $i < $this->data('ERROR_COUNT', 0); $i++) {
                    $messages[] = $this->data('ERROR_' . $i);
                }
            } else {
                $messages[] = $this->errorKey();
            }
        }
        return $messages;
    }

    public function merchant()
    {
        return $this->data('MERC');
    }

    public function session()
    {
        return $this->data('SESS');
    }

    public function order()
    {
        return $this->data('ORDR');
    }
}

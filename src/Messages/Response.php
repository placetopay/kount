<?php

namespace PlacetoPay\Kount\Messages;

class Response
{
    protected $raw;
    protected $data;

    public function __construct(string $response)
    {
        $this->raw = $response;

        $lines = preg_split('/[\r\n]+/', $response, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);
            $this->data[$key] = $value;
        }
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function data(?string $key = null, ?string $default = null): ?string
    {
        if (is_null($key)) {
            return $this->data;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }
}

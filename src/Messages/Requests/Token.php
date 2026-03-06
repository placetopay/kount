<?php

namespace PlacetoPay\Kount\Messages\Requests;

class Token extends Base
{
    private const SANDBOX_TOKEN_URL = 'https://login.kount.com/oauth2/ausdppkujzCPQuIrY357/v1/token';
    private const TOKEN_URL = 'https://login.kount.com/oauth2/ausdppksgrbyM0abp357/v1/token';

    private string $apiKey;

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . $this->apiKey,
        ];
    }

    public function query(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'scope' => 'k1_integration_api',
        ];
    }

    public function url(): string
    {
        return $this->sandbox ? self::SANDBOX_TOKEN_URL : self::TOKEN_URL;
    }
}

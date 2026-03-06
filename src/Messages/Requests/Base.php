<?php

namespace PlacetoPay\Kount\Messages\Requests;

abstract class Base
{
    private const SANDBOX_ORDERS_URL = 'https://api-sandbox.kount.com/commerce/v2/orders';
    private const ORDERS_URL = 'https://api.kount.com/commerce/v2/orders';

    protected bool $sandbox = false;
    protected array $data;
    protected string $token;
    protected string $channel = '';
    protected array $requestData = [];

    public function body(): array
    {
        return [];
    }
    public function query(): array
    {
        return [];
    }

    public function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function method(): string
    {
        return 'POST';
    }

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function setSandbox(bool $sandbox): self
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function setBearerToken(string $apiToken): self
    {
        $this->token = $apiToken;

        return $this;
    }

    public function url(): string
    {
        return $this->baseUrl();
    }

    private function baseUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_ORDERS_URL : self::ORDERS_URL;
    }
}

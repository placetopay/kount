<?php

namespace PlacetoPay\Kount\Messages\Responses;

use GuzzleHttp\Psr7\Response;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\ArrayHelper;

class Base
{
    protected Response $response;
    protected array $data = [];

    /**
     * @throws KountServiceException
     */
    public function __construct(Response $response)
    {
        $this->response = $response;

        $this->decodeJson();
    }

    protected function decodeJson(): void
    {
        try {
            $body = $this->response->getBody()->getContents();

            $this->data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new KountServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function toArray(): array
    {
        if (isset($this->data['fault'])) {
            $this->data['error'] = [
                'code' => $this->response->getStatusCode(),
                'message' => $this->data['fault']['faultstring'] ?? 'Unknown error',
            ];
        }

        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return ArrayHelper::get($this->data, $key, $default);
    }

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300 && empty($this->errors());
    }

    public function raw(): string
    {
        return (string)$this->response->getBody();
    }

    public function errors(): mixed
    {
        return $this->get('error', $this->get('errors')) ?? null;
    }
}

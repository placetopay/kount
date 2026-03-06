<?php

namespace PlacetoPay\Kount;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\Requests\Base;
use PlacetoPay\Kount\Messages\Requests\CreateOrder;
use PlacetoPay\Kount\Messages\Requests\InquiryOrder;
use PlacetoPay\Kount\Messages\Responses\ChargebackOrder;
use PlacetoPay\Kount\Messages\Responses\GetOrder;
use PlacetoPay\Kount\Messages\Responses\Token;
use PlacetoPay\Kount\Messages\Responses\UpdateOrder;

class KountService
{
    protected string $clientId;
    protected string $apiKey;
    protected string $channel;
    protected bool $sandbox = false;

    protected Client $client;

    /**
     * @throws KountServiceException
     */
    public function __construct($settings)
    {
        $this->validateMandatoryData($settings);

        $this->apiKey = $settings['apiKey'];
        $this->clientId = $settings['merchant'];
        $this->channel = $settings['website'];
        $this->client = $settings['client'] ?? new Client();

        if (isset($settings['sandbox'])) {
            $this->sandbox = filter_var($settings['sandbox'], FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function getSettings(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'clientId' => $this->clientId,
            'channel' => $this->channel,
            'sandbox' => $this->sandbox,
        ];
    }

    /**
     * @throws KountServiceException
     */
    private function validateMandatoryData($settings): void
    {
        if (!isset($settings['apiKey']) || !isset($settings['merchant']) || !isset($settings['website'])) {
            throw new KountServiceException('Values for apiKey, website or merchant has to be provided');
        }
    }

    /**
     * @throws GuzzleException
     * @throws KountServiceException
     */
    public function token(): Token
    {
        return new Token(
            $this->makeRequest(
                (new Messages\Requests\Token())
                    ->setApiKey($this->apiKey)
                    ->setSandbox($this->sandbox)
            )
        );
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function inquiryOrder(string $token, array|InquiryOrder $request): Messages\Responses\InquiryOrder
    {
        if (!($request instanceof InquiryOrder)) {
            $request = new InquiryOrder($request);
        }

        $request
            ->setBearerToken($token)
            ->setSandbox($this->sandbox)
            ->setChannel($this->channel);

        return new Messages\Responses\InquiryOrder($this->makeRequest($request));
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function createOrder(string $token, array|CreateOrder $request): Messages\Responses\InquiryOrder
    {
        if (!($request instanceof CreateOrder)) {
            $request = new CreateOrder($request);
        }

        $request
            ->setBearerToken($token)
            ->setSandbox($this->sandbox)
            ->setChannel($this->channel);

        return new Messages\Responses\InquiryOrder($this->makeRequest($request));
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function updateOrder(string $token, array|Messages\Requests\UpdateOrder $request): UpdateOrder
    {
        if (!($request instanceof Messages\Requests\UpdateOrder)) {
            $request = new Messages\Requests\UpdateOrder($request);
        }

        $request
            ->setBearerToken($token)
            ->setSandbox($this->sandbox)
            ->setChannel($this->channel);

        return new UpdateOrder($this->makeRequest($request));
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function notifyChargeback(string $token, array|Messages\Requests\ChargebackOrder $request): ChargebackOrder
    {
        if (!($request instanceof Messages\Requests\ChargebackOrder)) {
            $request = new Messages\Requests\ChargebackOrder($request);
        }

        $request
            ->setBearerToken($token)
            ->setSandbox($this->sandbox)
            ->setChannel($this->channel);

        return new ChargebackOrder($this->makeRequest($request));
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function getOrder(string $token, string $orderId): GetOrder
    {
        $request = new Messages\Requests\GetOrder([
            'orderId' => $orderId,
        ]);

        $request
            ->setBearerToken($token)
            ->setSandbox($this->sandbox);

        return new GetOrder($this->makeRequest($request));
    }

    /**
     * @throws GuzzleException|KountServiceException
     */
    private function makeRequest(Base $request): Response
    {
        try {
            $options = [
                'headers' => $request->headers(),
            ];

            if ($request->body()) {
                $options['json'] = $request->body();
            }

            if ($request->query()) {
                $options['query'] = $request->query();
            }

            return $this->client->{$request->method()}($request->url(), $options);
        } catch (RequestException $exception) {
            return $exception->getResponse();
        } catch (\Throwable $exception) {
            throw new KountServiceException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}

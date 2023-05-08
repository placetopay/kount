<?php

namespace PlacetoPay\Kount;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryRequest;
use PlacetoPay\Kount\Messages\InquiryResponse;
use PlacetoPay\Kount\Messages\Request;
use PlacetoPay\Kount\Messages\UpdateRequest;
use PlacetoPay\Kount\Messages\UpdateResponse;

class KountService
{
    private const DDC_URL = 'https://ssl.kaptcha.com';
    private const RIS_URL = 'https://risk.kount.net';
    private const SANDBOX_DDC_URL = 'https://tst.kaptcha.com';
    private const SANDBOX_RIS_URL = 'https://risk.test.kount.net';

    private const VERSION = '0720';

    protected $merchant;
    protected $apiKey;
    protected $website;

    protected $sandbox = false;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @throws KountServiceException
     */
    public function __construct($settings)
    {
        $this->validateMandatoryData($settings);

        $this->apiKey = $settings['apiKey'];
        $this->merchant = $settings['merchant'];
        $this->website = $settings['website'];

        $this->client = $settings['client'] ?? new Client();

        if (isset($settings['sandbox'])) {
            $this->sandbox = filter_var($settings['sandbox'], FILTER_VALIDATE_BOOLEAN);
        }
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

    public function parseInquiryRequest($session, $request)
    {
        if (!($request instanceof InquiryRequest)) {
            $request = new InquiryRequest($session, $request);
        }

        $request
            ->setApiToken($this->apiKey)
            ->setVersion(self::VERSION)
            ->setMerchant($this->merchant)
            ->setWebsite($this->website);

        return $request;
    }

    public function parseInquiryUpdate($session, $request)
    {
        if (!($request instanceof UpdateRequest)) {
            $request = new UpdateRequest($session, $request);
        }

        $request
            ->setApiToken($this->apiKey)
            ->setVersion(self::VERSION)
            ->setMerchant($this->merchant)
            ->setWebsite($this->website);

        return $request;
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function inquiry(string $session, $request): InquiryResponse
    {
        $request = $this->parseInquiryRequest($session, $request);

        return new InquiryResponse($this->makeRequest($request));
    }

    /**
     * @throws KountServiceException|GuzzleException
     */
    public function update($session, $request): UpdateResponse
    {
        $request = $this->parseInquiryUpdate($session, $request);

        return new UpdateResponse($this->makeRequest($request));
    }

    /**
     * @throws GuzzleException
     */
    private function makeRequest(Request $request): string
    {
        $response = $this->client->post(
            $this->risUrl(),
            [
                'headers' => $request->asRequestHeaders(),
                'form_params' => $request->asRequestData(),
            ]
        );
        return $response->getBody()->getContents();
    }

    public function dataCollectorUrl($session, $slug): string
    {
        $url = $this->isSandbox() ? self::SANDBOX_DDC_URL : self::DDC_URL;

        return $url . '/' . $slug . '?m=' . $this->merchant . '&s=' . $session;
    }

    public function risUrl(): string
    {
        return $this->isSandbox() ? self::SANDBOX_RIS_URL : self::RIS_URL;
    }

    public function isSandbox()
    {
        return $this->sandbox;
    }
}

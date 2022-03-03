<?php

namespace PlacetoPay\Kount;

use GuzzleHttp\Client;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryRequest;
use PlacetoPay\Kount\Messages\InquiryResponse;
use PlacetoPay\Kount\Messages\Request;
use PlacetoPay\Kount\Messages\UpdateRequest;
use PlacetoPay\Kount\Messages\UpdateResponse;

class KountService
{
    protected $DDC_URL = 'https://ssl.kaptcha.com';
    protected $RIS_URL = 'https://risk.kount.net';
    protected $SANDBOX_DDC_URL = 'https://tst.kaptcha.com';
    protected $SANDBOX_RIS_URL = 'https://risk.test.kount.net';

    protected $merchant;
    protected $apiKey;
    protected $website;

    protected $version = '0630';
    protected $sdkVersion = 'PlacetoPay-0.0.1';

    protected $sandbox = false;

    /**
     * @var Client
     */
    protected $client;

    public function __construct($settings)
    {
        if (!isset($settings['apiKey']) || !isset($settings['merchant']) || !isset($settings['website'])) {
            throw new KountServiceException('Values for apiKey, website or merchant has to be provided');
        }

        $this->apiKey = $settings['apiKey'];
        $this->merchant = $settings['merchant'];
        $this->website = $settings['website'];

        if (!isset($settings['client'])) {
            $this->client = new Client();
        } else {
            $this->client = $settings['client'];
        }

        if (isset($settings['sandbox'])) {
            $this->sandbox = filter_var($settings['sandbox'], FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function parseInquiryRequest($session, $request)
    {
        if (!($request instanceof InquiryRequest)) {
            $request = new InquiryRequest($session, $request);
        }

        $request->setApiToken($this->apiKey)
            ->setVersion($this->version)
            ->setSdkVersion($this->sdkVersion)
            ->setMerchant($this->merchant)
            ->setWebsite($this->website);

        return $request;
    }

    public function parseInquiryUpdate($session, $request)
    {
        if (!($request instanceof UpdateRequest)) {
            $request = new UpdateRequest($session, $request);
        }

        $request->setApiToken($this->apiKey)
            ->setVersion($this->version)
            ->setSdkVersion($this->sdkVersion)
            ->setMerchant($this->merchant)
            ->setWebsite($this->website);

        return $request;
    }

    public function inquiry(string $session, $request): InquiryResponse
    {
        $request = $this->parseInquiryRequest($session, $request);
        return new InquiryResponse($this->makeRequest($request));
    }

    public function update($session, $request)
    {
        $request = $this->parseInquiryUpdate($session, $request);
        return new UpdateResponse($this->makeRequest($request));
    }

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

    public function dataCollectorUrl($session, $slug)
    {
        if ($this->isSandbox()) {
            $url = $this->SANDBOX_DDC_URL;
        } else {
            $url = $this->DDC_URL;
        }
        return $url . '/' . $slug . '?m=' . $this->merchant . '&s=' . $session;
    }

    public function risUrl()
    {
        if ($this->isSandbox()) {
            return $this->SANDBOX_RIS_URL;
        }

        return $this->RIS_URL;
    }

    public function isSandbox()
    {
        return $this->sandbox;
    }
}

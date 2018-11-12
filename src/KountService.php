<?php


namespace PlacetoPay\Kount;


use PlacetoPay\Kount\Carrier\HttpCarrier;
use PlacetoPay\Kount\Contracts\Carrier;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryRequest;
use PlacetoPay\Kount\Messages\InquiryResponse;
use PlacetoPay\Kount\Messages\UpdateRequest;
use PlacetoPay\Kount\Messages\UpdateResponse;

class KountService
{

    protected $DDC_URL = 'https://prd.kaptcha.com';
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
     * @var Carrier
     */
    protected $carrier;

    public function __construct($settings)
    {
        if (!isset($settings['apiKey']) || !isset($settings['merchant']) || !isset($settings['website'])) {
            throw new KountServiceException('Values for apiKey, website or merchant has to be provided');
        }

        $this->apiKey = $settings['apiKey'];
        $this->merchant = $settings['merchant'];
        $this->website = $settings['website'];

        if (!isset($settings['carrier'])) {
            $this->carrier = new HttpCarrier();
        } else {
            $this->carrier = $settings['carrier'];
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

    /**
     * @param string $session
     * @param InquiryRequest|array $request
     * @return InquiryResponse
     * @throws KountServiceException
     */
    public function inquiry($session, $request)
    {
        $request = $this->parseInquiryRequest($session, $request);
        try {
            $result = $this->carrier->riskRequest($this->risUrl(), 'POST', $request->asRequestData(), $request->asRequestHeaders());
            return new InquiryResponse($result);
        } catch (\Exception $e) {
            throw new KountServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $session
     * @param UpdateRequest|array $request
     * @return UpdateResponse
     * @throws KountServiceException
     */
    public function update($session, $request)
    {
        $request = $this->parseInquiryUpdate($session, $request);
        try {
            $result = $this->carrier->riskRequest($this->risUrl(), 'POST', $request->asRequestData(), $request->asRequestHeaders());
            return new UpdateResponse($result);
        } catch (\Exception $e) {
            throw new KountServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function dataCollectorUrl($session, $slug)
    {
        if($this->isSandbox()) {
            $url = $this->SANDBOX_DDC_URL;
        } else {
            $url = $this->DDC_URL;
        }
        return $url . '/' . $slug . '?m=' . $this->merchant . '&s=' . $session;
    }

    public function risUrl()
    {
        if($this->isSandbox())
            return $this->SANDBOX_RIS_URL;

        return $this->RIS_URL;
    }

    public function isSandbox()
    {
        return $this->sandbox;
    }

}
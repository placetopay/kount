<?php


namespace PlacetoPay\Kount;


use PlacetoPay\Kount\Carrier\HttpCarrier;
use PlacetoPay\Kount\Contracts\Carrier;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryRequest;
use PlacetoPay\Kount\Messages\InquiryResponse;

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

    protected $sandbox = true;

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

    /**
     * @param string $session
     * @param InquiryRequest|array $request
     * @return InquiryResponse
     */
    public function inquiry($session, $request)
    {
        $request = $this->parseInquiryRequest($session, $request);
        $result = $this->carrier->riskRequest($this->url(), 'POST', $request->asRequestData(), $request->asRequestHeaders());
        return new InquiryResponse($result);
    }

    public function url()
    {
        return $this->SANDBOX_RIS_URL;
    }


}
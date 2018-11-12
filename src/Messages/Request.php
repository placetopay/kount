<?php


namespace PlacetoPay\Kount\Messages;


abstract class Request
{

    // Normal inquiry
    const MODE_INQUIRY = 'Q';
    // Phone order inquiry
    const MODE_PHONE_ORDER = 'P';
    // Kount Central normal inquiry with thresholds
    const MODE_THRESHOLDS = 'W';
    // Kount Central thresholds-only inquiry
    const MODE_THRESHOLDS_ONLY = 'J';
    // Update status with response
    const MODE_UPDATE = 'X';

    public static $MODES = [
        self::MODE_INQUIRY,
        self::MODE_PHONE_ORDER,
        self::MODE_THRESHOLDS,
        self::MODE_THRESHOLDS_ONLY,
        self::MODE_UPDATE,
    ];

    // Same day shipping
    const SHIP_SAME = 'SD';
    // Next day shipping
    const SHIP_NEXT = 'ND';
    // Second day shipping
    const SHIP_SECOND = '2D';
    // Standard shipping
    const SHIP_STANDARD = 'ST';

    const STAT_APPROVED = 'A';
    const STAT_DECLINED = 'D';

    public static $SHIPPINGS = [
        self::SHIP_SAME,
        self::SHIP_NEXT,
        self::SHIP_SECOND,
        self::SHIP_STANDARD,
    ];

    protected $mode;

    // Origin Obtained
    protected $version;
    protected $sdk = 'PHP';
    protected $sdkVersion;
    protected $apiToken;
    protected $merchant;
    protected $website;

    protected $session;
    protected $data;

    public function __construct($session, $data = [])
    {
        $this->session = $session;
        $this->data = $data;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
        return $this;
    }

    public function setSdkVersion($sdkVersion)
    {
        $this->sdkVersion = $sdkVersion;
        return $this;
    }

    public function setMerchant($merchant)
    {
        $this->merchant = $merchant;
        return $this;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return array
     */
    public abstract function asRequestData();

    /**
     * @return array
     */
    public function asRequestHeaders()
    {
        return [
            'X-Kount-Api-Key' => $this->apiToken,
        ];
    }

    protected function parseAmount($total)
    {
       return (int)(round($total, 2) * 100);
    }

}
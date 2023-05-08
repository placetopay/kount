<?php

namespace PlacetoPay\Kount\Messages;

abstract class Request
{
    // Normal inquiry
    public const MODE_INQUIRY = 'Q';

    // Phone order inquiry
    public const MODE_PHONE_ORDER = 'P';

    // Kount Central normal inquiry with thresholds
    public const MODE_THRESHOLDS = 'W';

    // Kount Central thresholds-only inquiry
    public const MODE_THRESHOLDS_ONLY = 'J';

    // Update status with response
    public const MODE_UPDATE = 'U';

    public static $MODES = [
        self::MODE_INQUIRY,
        self::MODE_PHONE_ORDER,
        self::MODE_THRESHOLDS,
        self::MODE_THRESHOLDS_ONLY,
        self::MODE_UPDATE,
    ];

    // Same day shipping
    public const SHIP_SAME = 'SD';

    // Next day shipping
    public const SHIP_NEXT = 'ND';

    // Second day shipping
    public const SHIP_SECOND = '2D';

    // Standard shipping
    public const SHIP_STANDARD = 'ST';

    public const STAT_APPROVED = 'A';
    public const STAT_DECLINED = 'D';

    public static $SHIPPINGS = [
        self::SHIP_SAME,
        self::SHIP_NEXT,
        self::SHIP_SECOND,
        self::SHIP_STANDARD,
    ];

    protected $mode;

    // Origin Obtained
    protected $version;
    protected $apiToken;
    protected $merchant;
    protected $website;

    protected $session;
    protected $data;

    /**
     * @return array
     */
    abstract public function asRequestData(): array;

    public function __construct($session, $data = [])
    {
        $this->session = $session;
        $this->data = $data;
    }

    public function setMode($mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function setVersion($version): self
    {
        $this->version = $version;

        return $this;
    }

    public function setApiToken($apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function setMerchant($merchant): self
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function setWebsite($website): self
    {
        $this->website = $website;

        return $this;
    }

    public function asRequestHeaders(): array
    {
        return [
            'X-Kount-Api-Key' => $this->apiToken,
        ];
    }

    protected function parseAmount($total, string $currency = 'COP'): int
    {
        $currenciesDecimals = [
            'BHD' => 3,
            'BIF' => 0,
            'CLF' => 4,
            'CLP' => 0,
            'DJF' => 0,
            'GNF' => 0,
            'IQD' => 3,
            'ISK' => 0,
            'JOD' => 3,
            'JPY' => 0,
            'KMF' => 0,
            'KRW' => 0,
            'KWD' => 3,
            'LYD' => 3,
            'OMR' => 3,
            'PYG' => 0,
            'RWF' => 0,
            'TND' => 3,
            'UGX' => 0,
            'UYI' => 0,
            'UYW' => 4,
            'VND' => 0,
            'VUV' => 0,
            'XAF' => 0,
            'XOF' => 0,
            'XPF' => 0,
        ];
        $decimals = $currenciesDecimals[$currency] ?? 2;

        return (int)(round($total, $decimals) * pow(10, $decimals));
    }
}

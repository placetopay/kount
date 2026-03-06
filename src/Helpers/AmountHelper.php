<?php

namespace PlacetoPay\Kount\Helpers;

class AmountHelper
{
    public static function parseAmount($total, string $currency = 'COP', bool $isMinorUnit = false): int
    {
        if ($isMinorUnit) {
            return (int)$total;
        }

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

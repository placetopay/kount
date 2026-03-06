<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PlacetoPay\Kount\Helpers\MockClient;
use PlacetoPay\Kount\KountService;

class BaseTestCase extends TestCase
{
    public function service(array $overrides = []): KountService
    {
        return new KountService(array_merge([
            'client' => MockClient::client(),
            'merchant' => getenv('MERCHANT') ?: 'YOUR_MERCHANT',
            'apiKey' => getenv('APIKEY') ?: 'THE_API_KEY_PROVIDED',
            'website' => getenv('WEBSITE') ?: 'THE_AWC_SITE_NAME',
            'sandbox' => true,
        ], $overrides));
    }
}

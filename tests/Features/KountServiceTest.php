<?php

namespace Tests\Features;

use GuzzleHttp\Psr7\Response;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\KountService;
use PlacetoPay\Kount\Messages\Responses\Token;
use Tests\BaseTestCase;

class KountServiceTest extends BaseTestCase
{
    /** @test */
    public function itCanInstantiateTheService(): void
    {
        $settings = [
            'apiKey' => 'testingValues',
            'sandbox' => true,
            'merchant' => 'testingValues',
            'website' => 'testingValues',
        ];

        $service = new KountService($settings);

        $this->assertEquals([
            'apiKey' => 'testingValues',
            'sandbox' => true,
            'clientId' => 'testingValues',
            'channel' => 'testingValues',
        ], $service->getSettings());
    }

    /** @test */
    public function itFailsDecodingJson(): void
    {
        $this->expectException(KountServiceException::class);

        new Token(new Response(200, [], 'testing', '1.1'));
    }

    /** @test */
    public function itCannotInstantiateTheServiceMissingApiKey(): void
    {
        $this->expectException(KountServiceException::class);
        $this->expectExceptionMessage('Values for apiKey, website or merchant has to be provided');

        new KountService([
            'merchant' => 'testingValues',
            'website' => 'testingValues',
            'sandbox' => true,
        ]);
    }

    /** @test */
    public function itCannotInstantiateTheServiceMissingMerchant(): void
    {
        $this->expectException(KountServiceException::class);
        $this->expectExceptionMessage('Values for apiKey, website or merchant has to be provided');

        new KountService([
            'apiKey' => 'testingValues',
            'website' => 'testingValues',
            'sandbox' => true,
        ]);
    }

    /** @test */
    public function itCannotInstantiateTheServiceMissingWebsite(): void
    {
        $this->expectException(KountServiceException::class);
        $this->expectExceptionMessage('Values for apiKey, website or merchant has to be provided');

        new KountService([
            'apiKey' => 'testingValues',
            'merchant' => 'testingValues',
            'sandbox' => true,
        ]);
    }
}

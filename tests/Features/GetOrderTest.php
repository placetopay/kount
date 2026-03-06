<?php

namespace Tests\Features;

use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\ArrayHelper;
use PlacetoPay\Kount\Helpers\MockClient;
use Tests\BaseTestCase;
use Tests\Traits\HasOrderStructure;

class GetOrderTest extends BaseTestCase
{
    use HasOrderStructure;

    /**
     * @test
     */
    public function itCanInquiryAnOrder(): void
    {
        $response = $this->service()->getOrder(MockClient::VALID_API_TOKEN, 'ORDER12345');

        $this->assertTrue($response->successful());
    }

    /**
     * @test
     * @dataProvider behavioursProvider
     */
    public function itCanGetAnOrderWithExpectedRisk(string $orderId): void
    {
        $response = $this->service()->getOrder(MockClient::VALID_API_TOKEN, $orderId);

        $this->assertTrue($response->successful());
        $this->assertEquals(200, $response->status());
        $this->assertEquals(ArrayHelper::get($response->toArray(), 'order.riskInquiry.decision'), $orderId);
    }

    /**
     * @test
     */
    public function itCannotGetAnOrderUnexpectedException(): void
    {
        $this->expectException(KountServiceException::class);
        $this->service()->getOrder(MockClient::VALID_API_TOKEN, 'EXCEPTION');
    }

    /**
     * @test
     */
    public function itCannotGetAnOrder(): void
    {
        $response = $this->service()->getOrder(MockClient::VALID_API_TOKEN, 'NOT_FOUND');

        $this->assertFalse($response->successful());
        $this->assertEquals(404, $response->status());
    }
}

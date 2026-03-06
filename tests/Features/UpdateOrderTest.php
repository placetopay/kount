<?php

namespace Tests\Features;

use PlacetoPay\Kount\Constants\DecisionCodes;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\MockClient;
use PlacetoPay\Kount\Messages\Requests\UpdateOrder;
use Tests\BaseTestCase;
use Tests\Traits\HasOrderStructure;

class UpdateOrderTest extends BaseTestCase
{
    use HasOrderStructure;

    /**
     * @test
     */
    public function itCanUpdateAnOrder(): void
    {
        $request = [
            'orderId' => 'ORDER12345',
            'payment' => [
                'reference' => 'ORDER12345',
            ],
            'kountSessionId' => 'SESSION12345',
            'riskInquiry' => [
                'decision' => DecisionCodes::APPROVE,
                'reasonCode' => DecisionCodes::APPROVE,
            ],
        ];

        $response = $this->service()->updateOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
    }

    /**
     * @test
     */
    public function itCannotUpdateAnOrderMissingOrderId(): void
    {
        $this->expectException(KountServiceException::class);
        $request = [
            'payment' => [
                'reference' => 'ORDER12345',
            ],
            'kountSessionId' => 'SESSION12345',
        ];

        $this->service()->updateOrder(MockClient::VALID_API_TOKEN, $request);
    }

    /**
     * @test
     */
    public function itCannotUpdateAnOrderUnexpectedException(): void
    {
        $this->expectException(KountServiceException::class);
        $request = [
            'orderId' => 'EXCEPTION',
            'payment' => [
                'reference' => 'ORDER12345',
            ],
            'kountSessionId' => 'SESSION12345',
        ];

        $this->service()->updateOrder(MockClient::VALID_API_TOKEN, $request);
    }

    /**
     * @test
     */
    public function itCannotUpdateAnOrderValidationErrorOrder(): void
    {
        $request = [
            'orderId' => 'VALIDATION_ERROR',
            'payment' => [
                'reference' => 'ORDER12345',
            ],
            'kountSessionId' => 'SESSION12345',
        ];

        $response = $this->service()->updateOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(400, $response->status());
    }

    /**
     * @test
     */
    public function itCannotUpdateAnOrderNotFoundOrder(): void
    {
        $request = [
            'orderId' => 'NOT_FOUND',
            'payment' => [
                'reference' => 'ORDER12345',
            ],
            'kountSessionId' => 'SESSION12345',
        ];

        $response = $this->service()->updateOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(404, $response->status());
    }

    /**
     * @test
     */
    public function itCanParseCorrectly(): void
    {
        $request = [
            'orderId' => 'ORDER12345',
            'payment' => [
                'reference' => 'testing_reference',
            ],
            'kountSessionId' => 'testing_kountSessionId',
            'riskInquiry' => [
                'decision' => DecisionCodes::APPROVE,
                'reasonCode' => DecisionCodes::APPROVE,
            ],
        ];

        $new = new UpdateOrder($request);

        $this->assertEquals([
            'merchantOrderId' => 'testing_reference',
            'deviceSessionId' => 'testing_kountSessionId',
            'riskInquiry' => [
                'decision' => DecisionCodes::APPROVE,
                'reasonCode' => DecisionCodes::APPROVE,
            ],
        ], $new->body());

        $request = [
            'orderId' => 'ORDER12345',
            'payment' => [
                'reference' => 'testing_reference',
            ],
            'kountSessionId' => 'testing_kountSessionId',
        ];

        $new = new UpdateOrder($request);

        $this->assertEquals([
            'merchantOrderId' => 'testing_reference',
            'deviceSessionId' => 'testing_kountSessionId',
        ], $new->body());
    }
}

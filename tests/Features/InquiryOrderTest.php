<?php

namespace Tests\Features;

use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\MockClient;
use Tests\BaseTestCase;
use Tests\Traits\HasOrderStructure;

class InquiryOrderTest extends BaseTestCase
{
    use HasOrderStructure;

    /**
     * @test
     */
    public function itCanInquiryAnOrder(): void
    {
        $request = $this->getOrderRequestStructure();

        $response = $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
    }

    /**
     * @test
     * @dataProvider behavioursProvider
     */
    public function itCanInquiryAnOrderWithBehaviours(string $behaviour): void
    {
        $request = $this->getOrderRequestStructure([
            'payment' => [
                'reference' => $behaviour,
            ],
        ]);

        $response = $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
        $this->assertEquals(200, $response->status());
        $this->assertEquals($behaviour, $response->decision());
        $this->assertTrue(match ($response->decision()) {
            'APPROVE' => $response->shouldApprove(),
            'REVIEW' => $response->shouldReview(),
            'DECLINE' => $response->shouldDecline(),
            default => false,
        });
    }

    /**
     * @test
     */
    public function itCannotInquiryAnOrderWithUnexpectedException(): void
    {
        $this->expectException(KountServiceException::class);
        $request = $this->getOrderRequestStructure([
            'payment' => [
                'reference' => 'EXCEPTION',
            ],
        ]);

        $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);
    }

    /**
     * @test
     */
    public function itCannotInquiryAnOrderWithValidationError(): void
    {
        $request = $this->getOrderRequestStructure([
            'payment' => [
                'reference' => 'VALIDATION_ERROR',
            ],
        ]);

        $response = $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(400, $response->status());
    }

    /**
     * @test
     */
    public function itCannotInquiryAnOrderWithInvalidToken(): void
    {
        $request = $this->getOrderRequestStructure();

        $response = $this->service()->inquiryOrder(MockClient::INVALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(401, $response->status());
    }
}

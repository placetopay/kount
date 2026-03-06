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
        $this->assertTrue($response->order()->riskInquiry()->device()->wasVerifiedUsingDevice());
        $this->assertIsArray($response->order()->riskInquiry()->rulesTriggered());
        $this->assertEquals(99.9, $response->order()->riskInquiry()->omniscore());
        $this->assertEquals('US', $response->order()->riskInquiry()->persona()->riskiestCountry());
        $this->assertEquals(3, $response->order()->riskInquiry()->persona()->totalBankApprovedOrders());
        $this->assertEquals(2, $response->order()->riskInquiry()->persona()->maxVelocity());
        $this->assertEquals(3, $response->order()->riskInquiry()->persona()->uniqueCards());
        $this->assertEquals(2, $response->order()->riskInquiry()->persona()->uniqueEmails());
        $this->assertEquals(5, $response->order()->riskInquiry()->persona()->uniqueDevices());
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
        $this->assertEquals($behaviour, $response->order()->riskInquiry()->decision());
        $this->assertTrue(match ($response->order()->riskInquiry()->decision()) {
            'APPROVE' => $response->order()->riskInquiry()->shouldApprove(),
            'REVIEW' => $response->order()->riskInquiry()->shouldReview(),
            'DECLINE' => $response->order()->riskInquiry()->shouldDecline(),
            default => false,
        });
    }

    /**
     * @test
     */
    public function itCanInquiryAnOrderWithToken(): void
    {
        $request = $this->getOrderRequestStructure([
            'instrument' => [
                'token' => 'testing_token',
            ],
        ]);

        unset($request['instrument']['card']);

        $response = $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     */
    public function itCanInquiryAnOrderWithoutInstrument(): void
    {
        $request = $this->getOrderRequestStructure();

        unset($request['instrument']);

        $response = $this->service()->inquiryOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
        $this->assertEquals(200, $response->status());
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

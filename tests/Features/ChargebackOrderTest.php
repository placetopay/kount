<?php

namespace Tests\Features;

use PlacetoPay\Kount\Constants\FraudReportTypes;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\MockClient;
use PlacetoPay\Kount\Messages\Requests\ChargebackOrder;
use Tests\BaseTestCase;
use Tests\Traits\HasOrderStructure;

class ChargebackOrderTest extends BaseTestCase
{
    use HasOrderStructure;

    /**
     * @test
     */
    public function itCanNotifyChargebackAnOrder(): void
    {
        $request = [
            'orderId' => 'ORDER12345',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargebacks' => [
                'transactionId' => uniqid(),
                'reasonCode' => '01',
                'cardType' => 'VISA',
            ],
        ];

        $response = $this->service()->notifyChargeback(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
    }

    /**
     * @test
     */
    public function itCannotNotifyChargebackAnOrderUnauthorizedUser(): void
    {
        $request = [
            'orderId' => 'ORDER12345',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargebacks' => [
                'transactionId' => uniqid(),
                'reasonCode' => '01',
                'cardType' => 'VISA',
            ],
        ];

        $response = $this->service()->notifyChargeback(MockClient::INVALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(401, $response->status());
        $this->assertEquals([
            'fault' => [
                'detail' => [
                    'errorcode' => 'custom',
                ],
                'faultstring' => '{"efxErrorCode": "401.04", "messageParams": ["Invalid Token"]}',
            ],
            'error' => [
                'code' => 401,
                'message' => '{"efxErrorCode": "401.04", "messageParams": ["Invalid Token"]}',
            ],
        ], $response->toArray());
    }

    /**
     * @test
     */
    public function itCannotNotifyChargebackAnOrderValidationError(): void
    {
        $request = [
            'orderId' => 'VALIDATION_ERROR',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargebacks' => [
                'transactionId' => uniqid(),
                'reasonCode' => '01',
                'cardType' => 'VISA',
            ],
        ];

        $response = $this->service()->notifyChargeback(MockClient::VALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     */
    public function itCannotNotifyChargebackAnOrderException(): void
    {
        $this->expectException(KountServiceException::class);

        $request = [
            'orderId' => 'EXCEPTION',
            'fraudReportType' => FraudReportTypes::OTHER,
        ];

        $this->service()->notifyChargeback(MockClient::VALID_API_TOKEN, $request);
    }

    /**
     * @test
     */
    public function itCanParseCorrectly(): void
    {
        $request = [
            'orderId' => 'EXCEPTION',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargeback' => [
                'transactionId' => 'testing_chargeback_transactionId',
                'reasonCode' => 'testing_reasonCode',
                'cardType' => 'testing_cardType',
            ],
            'refund' => [
                'transactionId' => 'testing_refund_TransactionId',
                'date' => '2024-06-10T12:00:00.000Z',
                'amount' => [
                    'total' => 1234,
                    'currency' => 'USD',
                    'inMinorUnit' => false,
                ],
                'receipt' => 'testing_refund_Receipt',
            ],
        ];

        $request = new ChargebackOrder($request);

        $this->assertEquals(
            $request->body(),
            [
                'reversalsUpdates' => [
                    [
                        'orderId' => 'EXCEPTION',
                        'fraudReportType' => FraudReportTypes::OTHER,
                        'chargeback' => [
                            'isChargeback' => true,
                            'transactionId' => 'testing_chargeback_transactionId',
                            'reasonCode' => 'testing_reasonCode',
                            'cardType' => 'testing_cardType',
                        ],
                        'refund' => [
                            'isRefund' => true,
                            'transactionId' => 'testing_refund_TransactionId',
                            'dateTime' => '2024-06-10T12:00:00.000Z',
                            'amount' => 123400,
                            'currency' => 'USD',
                            'gatewayReceipt' => 'testing_refund_Receipt',
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @test
     */
    public function itCanParseCorrectlyWithIntAmount(): void
    {
        $request = [
            'orderId' => 'EXCEPTION',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargeback' => [
                'transactionId' => 'testing_chargeback_transactionId',
                'reasonCode' => 'testing_reasonCode',
                'cardType' => 'testing_cardType',
            ],
            'refund' => [
                'transactionId' => 'testing_refund_TransactionId',
                'date' => '2024-06-10T12:00:00.000Z',
                'amount' => [
                    'total' => 1234,
                    'currency' => 'USD',
                    'inMinorUnit' => true,
                ],
                'receipt' => 'testing_refund_Receipt',
            ],
        ];

        $request = new ChargebackOrder($request);

        $this->assertEquals(
            $request->body(),
            [
                'reversalsUpdates' => [
                    [
                        'orderId' => 'EXCEPTION',
                        'fraudReportType' => FraudReportTypes::OTHER,
                        'chargeback' => [
                            'isChargeback' => true,
                            'transactionId' => 'testing_chargeback_transactionId',
                            'reasonCode' => 'testing_reasonCode',
                            'cardType' => 'testing_cardType',
                        ],
                        'refund' => [
                            'isRefund' => true,
                            'transactionId' => 'testing_refund_TransactionId',
                            'dateTime' => '2024-06-10T12:00:00.000Z',
                            'amount' => 1234,
                            'currency' => 'USD',
                            'gatewayReceipt' => 'testing_refund_Receipt',
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @test
     */
    public function itCanParseCorrectlyWithMissingInformation(): void
    {
        $request = [
            'orderId' => 'EXCEPTION',
            'fraudReportType' => FraudReportTypes::OTHER,
            'chargeback' => [
                'transactionId' => 'testing_chargeback_transactionId',
                'reasonCode' => 'testing_reasonCode',
                'cardType' => 'testing_cardType',
            ],
            'refund' => [
                'transactionId' => 'testing_refund_TransactionId',
                'date' => '2024-06-10T12:00:00.000Z',
                'amount' => [
                    'total' => 1234,
                ],
                'receipt' => 'testing_refund_Receipt',
            ],
        ];

        $request = new ChargebackOrder($request);

        $this->assertEquals(
            $request->body(),
            [
                'reversalsUpdates' => [
                    [
                        'orderId' => 'EXCEPTION',
                        'fraudReportType' => FraudReportTypes::OTHER,
                        'chargeback' => [
                            'isChargeback' => true,
                            'transactionId' => 'testing_chargeback_transactionId',
                            'reasonCode' => 'testing_reasonCode',
                            'cardType' => 'testing_cardType',
                        ],
                        'refund' => [
                            'isRefund' => true,
                            'transactionId' => 'testing_refund_TransactionId',
                            'dateTime' => '2024-06-10T12:00:00.000Z',
                            'gatewayReceipt' => 'testing_refund_Receipt',
                        ],
                    ],
                ],
            ],
        );
    }
}

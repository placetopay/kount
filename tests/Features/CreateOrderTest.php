<?php

namespace Tests\Features;

use PlacetoPay\Kount\Constants\ShippingMethods;
use PlacetoPay\Kount\Constants\ShippingTypes;
use PlacetoPay\Kount\Constants\TransactionStatuses;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\MockClient;
use PlacetoPay\Kount\Messages\Requests\CreateOrder;
use Tests\BaseTestCase;
use Tests\Traits\HasOrderStructure;

class CreateOrderTest extends BaseTestCase
{
    use HasOrderStructure;

    /**
     * @test
     */
    public function itCanCreateAnOrder(): void
    {
        $request = $this->getOrderRequestStructure();

        $response = $this->service()->createOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertTrue($response->successful());
    }

    /**
     * @test
     */
    public function itCannotCreateAnOrderWithValidationErrors(): void
    {
        $request = $this->getOrderRequestStructure([
            'payment' => [
                'reference' => 'VALIDATION_ERROR',
            ],
        ]);

        $response = $this->service()->createOrder(MockClient::VALID_API_TOKEN, $request);

        $this->assertFalse($response->successful());
        $this->assertEquals(400, $response->status());
    }

    /**
     * @test
     */
    public function itCannotCreateAnOrderWithException(): void
    {
        $this->expectException(KountServiceException::class);
        $request = $this->getOrderRequestStructure([
            'payment' => [
                'reference' => 'EXCEPTION',
            ],
        ]);

        $this->service()->createOrder(MockClient::VALID_API_TOKEN, $request);
    }

    /**
     * @test
     */
    public function itCanParseCorrectly(): void
    {
        $request = $this->getOrderRequestStructure(
            [
                'ipAddress' => '127.0.0.1',
                'date' => '2024-06-01T12:00:00.000Z',
                'payment' => [
                    'reference' => 'TESTING_REFERENCE',
                    'amount' => [
                        'subtotal' => 100,
                        'total' => 120,
                        'currency' => 'USD',
                        'inMinorUnit' => false,
                        'taxCountry' => 'US',
                        'taxes' => [
                            [
                                'kind' => 'iva',
                                'amount' => 15,
                            ],
                            [
                                'kind' => 'stateTax',
                                'amount' => 5,
                            ],
                        ],
                        'details' => [
                            [
                                'kind' => 'subtotal',
                                'amount' => 100,
                            ],
                            [
                                'kind' => 'shipping',
                                'amount' => 1500,
                            ],
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 'ITEM1',
                            'description' => 'Product 1',
                            'name' => 'Product 1',
                            'qty' => 1,
                            'sku' => 'SKU1',
                            'price' => 70,
                            'category' => 'digital',
                            'additional' => [
                                'isDigital' => true,
                                'subCategory' => 'software',
                                'upc' => '123456789012',
                                'brand' => 'BrandA',
                                'url' => 'https://example.com/item1',
                                'imageUrl' => 'https://example.com/item1.jpg',
                                'attributes' => [
                                    'color' => 'red',
                                    'size' => 'M',
                                    'weight' => '1kg',
                                    'height' => '10cm',
                                    'width' => '5cm',
                                    'depth' => '2cm',
                                ],
                                'descriptors' => ['Special edition'],
                                'isService' => false,
                            ],
                        ],
                        [
                            'id' => 'ITEM2',
                            'desc' => 'Product 2',
                            'qty' => 3,
                            'sku' => 'SKU2',
                            'price' => 30,
                        ],
                    ],
                    'shipping' => [
                        'name' => 'John',
                        'surname' => 'Doe',
                        'preferred' => 'Johnny',
                        'middle' => 'A',
                        'prefix' => 'Mr.',
                        'suffix' => 'Jr.',
                        'email' => 'john.doe@example.com',
                        'mobile' => '+1234567890',
                        'dateOfBirth' => '1990-01-01',
                        'address' => [
                            'street' => '123 Main St',
                            'city' => 'New York',
                            'state' => 'NY',
                            'country' => 'US',
                            'postalCode' => '10001',
                        ],
                    ],
                ],
                'account' => [
                    'id' => 'ACC123',
                    'type' => 'customer',
                    'creationDateTime' => '2020-01-01T00:00:00.000Z',
                    'username' => 'user1',
                    'accountIsActive' => true,
                ],
                'transaction' => [
                    'processor' => 'VISA',
                    'status' => TransactionStatuses::PENDING,
                    'authResult' => 'APPROVED',
                    'date' => '2024-06-01T12:05:00.000Z',
                    'verification' => ['cvvStatus' => '', 'avsStatus' => '2'],
                    'declineCode' => '00',
                    'authorization' => 'AUTHCODE123',
                    'processorId' => 'PROC123',
                    'receipt' => 'ARN123',
                ],
                'instrument' => [
                    'card' => [
                        'bin' => '411111',
                        'last4' => '1111',
                        'cardBrand' => 'VISA',
                    ],
                    'kount' => [
                        'session' => 'KountDeviceIdDDC',
                    ],
                ],
                'payer' => [
                    'name' => 'John',
                    'surname' => 'Doe',
                    'preferred' => 'Johnny',
                    'middle' => 'A',
                    'prefix' => 'Mr.',
                    'suffix' => 'Jr.',
                    'email' => 'john.doe@example.com',
                    'mobile' => '+1234567890',
                    'dateOfBirth' => '1990-01-01',
                    'address' => [
                        'street' => '123 Main St',
                        'street2' => 'Apt 4',
                        'city' => 'New York',
                        'state' => 'NY',
                        'country' => 'US',
                        'postalCode' => '10001',
                    ],
                ],
                'shipping' => [
                    'type' => ShippingTypes::LOCAL_DELIVERY,
                    'provider' => 'FedEx',
                    'trackingNumber' => 'TRACK123',
                    'method' => ShippingMethods::EXPRESS,
                    'store' => [
                        'id' => 'STORE001',
                        'name' => 'Main Store',
                        'address' => [
                            'street' => '456 Store St',
                            'city' => 'New York',
                            'country' => 'US',
                        ],
                    ],
                    'accessUrl' => 'www.google.com/downloadTesting',
                    'digitalDownloaded' => 'false',
                    'downloadDeviceIp' => '168.161.1.1',
                ],
                'promotions' => [
                    [
                        'id' => 'PROMO1',
                        'description' => '10% off',
                        'status' => 'active',
                        'statusReason' => 'seasonal',
                        'discount' => [
                            'percentage' => 10,
                            'amount' => 10,
                            'currency' => 'USD',
                        ],
                        'credit' => [
                            'creditType' => 'bonus',
                            'percentage' => 1,
                            'currency' => 'USD',
                            'amount' => 10,
                        ],
                    ],
                ],
                'loyalty' => [
                    'id' => 'LOYALTY1',
                    'description' => 'Loyalty program',
                    'credit' => [
                        'creditType' => 'GIFT_CARD',
                        'amount' => 100,
                        'currency' => 'USD',
                    ],
                ],
                'additional' => [
                    'customData1' => 'value1',
                    'customData2' => 'value2',
                ],
                'merchant' => [
                    'merchantCategoryCode' => 'testingMMC',
                    'name' => 'merchantName',
                    'storeName' => 'storeNameTesting',
                    'websiteUrl' => 'websiteUrlTesting',
                    'id' => 'idTesting123',
                    'contactEmail' => 'contactEmail@testing.com',
                    'contactPhoneNumber' => '30022211111',
                ],
                'links' => [
                    'viewOrderUrl' => 'testingUrlViewOrderUrl',
                    'requestRefundUrl' => 'testingUrlRequestRefundUrl',
                    'buyAgainUrl' => 'testingUrlBuyAgainUrl',
                    'writeReviewUrl' => 'testingUrlWriteReviewUrl',
                ],
                'advertising' => [
                    'channel' => 'website',
                    'affiliate' => 'testingAffiliate',
                    'subAffiliate' => 'testingSubAffiliate',
                    'writeReviewUrl' => 'testingWritingUrlReview',
                    'events' => [
                        [
                            'type' => 'event1Type',
                            'value' => 'event1TValue',
                        ],
                        [
                            'type' => 'event2Type',
                            'value' => 'event2TValue',
                        ],
                    ],
                    'campaign' => [
                        'id' => 'campaignId',
                        'name' => 'campaignName',
                    ],
                ],
            ],
        );

        $orderRequest = new CreateOrder($request);

        $this->assertEquals([
            'merchantOrderId' => 'TESTING_REFERENCE',
            'deviceSessionId' => 'KountDeviceIdDDC',
            'creationDateTime' => '2024-06-01T12:00:00.000Z',
            'items' => [
                [
                    'id' => 'ITEM1',
                    'price' => 7000,
                    'quantity' => 1,
                    'sku' => 'SKU1',
                    'category' => 'digital',
                    'isDigital' => true,
                    'subCategory' => 'software',
                    'upc' => '123456789012',
                    'brand' => 'BrandA',
                    'url' => 'https://example.com/item1',
                    'imageUrl' => 'https://example.com/item1.jpg',
                    'physicalAttributes' => [
                        'color' => 'red',
                        'size' => 'M',
                        'weight' => '1kg',
                        'height' => '10cm',
                        'width' => '5cm',
                        'depth' => '2cm',
                    ],
                    'descriptors' => [
                        'Special edition',
                    ],
                    'isService' => false,
                    'description' => 'Product 1',
                ],
                [
                    'id' => 'ITEM2',
                    'price' => 3000,
                    'quantity' => 3,
                    'sku' => 'SKU2',
                ],
            ],
            'userIp' => '127.0.0.1',
            'account' => [
                'id' => 'ACC123',
                'type' => 'customer',
                'creationDateTime' => '2020-01-01T00:00:00.000Z',
                'username' => 'user1',
                'accountIsActive' => true,
            ],
            'transactions' => [
                [
                    'processor' => 'VISA',
                    'processorMerchantId' => 'PROC123',
                    'payment' => [
                        'type' => 'CARD',
                        'bin' => '411111',
                        'last4' => '1111',
                        'cardBrand' => 'VISA',
                    ],
                    'subtotal' => 10000,
                    'orderTotal' => 12000,
                    'currency' => 'USD',
                    'merchantTransactionId' => 'TESTING_REFERENCE',
                    'tax' => [
                        'isTaxable' => true,
                        'taxAmount' => 2000,
                        'taxableCountryCode' => 'US',
                        'outOfStateTotal' => 500,
                    ],
                    'billedPerson' => [
                        'name' => [
                            'first' => 'John',
                            'family' => 'Doe',
                            'preferred' => 'Johnny',
                            'middle' => 'A',
                            'prefix' => 'Mr.',
                            'suffix' => 'Jr.',
                        ],
                        'emailAddress' => 'john.doe@example.com',
                        'phoneNumber' => '+1234567890',
                        'dateOfBirth' => '1990-01-01',
                        'address' => [
                            'line1' => '123 Main St',
                            'line2' => 'Apt 4',
                            'city' => 'New York',
                            'region' => 'NY',
                            'countryCode' => 'US',
                            'postalCode' => '10001',
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 'ITEM1',
                            'quantity' => 1,
                        ],
                        [
                            'id' => 'ITEM2',
                            'quantity' => 3,
                        ],
                    ],
                    'transactionStatus' => 'PENDING',
                    'authorizationStatus' => [
                        'authResult' => 'APPROVED',
                        'dateTime' => '2024-06-01T12:05:00.000Z',
                        'verificationResponse' => [
                            'avsStatus' => '2',
                        ],
                        'declineCode' => '00',
                        'processorAuthCode' => 'AUTHCODE123',
                        'processorTransactionId' => 'PROC123',
                        'acquirerReferenceNumber' => 'ARN123',
                    ],
                ],
            ],
            'promotions' => [
                [
                    'id' => 'PROMO1',
                    'description' => '10% off',
                    'status' => 'active',
                    'statusReason' => 'seasonal',
                    'discount' => [
                        'percentage' => 10,
                        'amount' => 1000,
                        'currency' => 'USD',
                    ],
                    'credit' => [
                        'creditType' => 'bonus',
                        'currency' => 'USD',
                        'amount' => 1000,
                    ],
                ],
            ],
            'loyalty' => [
                'id' => 'LOYALTY1',
                'description' => 'Loyalty program',
                'credit' => [
                    'creditType' => 'GIFT_CARD',
                    'amount' => 10000,
                    'currency' => 'USD',
                ],
            ],
            'fulfillment' => [
                [
                    'type' => 'LOCAL_DELIVERY',
                    'shipping' => [
                        'amount' => 150000,
                        'provider' => 'FedEx',
                        'trackingNumber' => 'TRACK123',
                        'method' => 'EXPRESS',
                    ],
                    'recipientPerson' => [
                        'name' => [
                            'first' => 'John',
                            'family' => 'Doe',
                            'preferred' => 'Johnny',
                            'middle' => 'A',
                            'prefix' => 'Mr.',
                            'suffix' => 'Jr.',
                        ],
                        'emailAddress' => 'john.doe@example.com',
                        'phoneNumber' => '+1234567890',
                        'dateOfBirth' => '1990-01-01',
                        'address' => [
                            'line1' => '123 Main St',
                            'line2' => 'Apt 4',
                            'city' => 'New York',
                            'region' => 'NY',
                            'countryCode' => 'US',
                            'postalCode' => '10001',
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 'ITEM1',
                            'quantity' => 1,
                        ],
                        [
                            'id' => 'ITEM2',
                            'quantity' => 3,
                        ],
                    ],
                    'accessUrl' => 'www.google.com/downloadTesting',
                    'downloadDeviceIp' => '168.161.1.1',
                    'merchantFulfillmentId' => 'TESTING_REFERENCE',
                    'digitalDownloaded' => false,
                    'store' => [
                        'id' => 'STORE001',
                        'name' => 'Main Store',
                        'address' => [
                            'line1' => '456 Store St',
                            'city' => 'New York',
                            'countryCode' => 'US',
                        ],
                    ],
                ],
            ],
            'customFields' => [
                'customData1' => 'value1',
                'customData2' => 'value2',
            ],
            'merchant' => [
                'name' => 'merchantName',
                'storeName' => 'storeNameTesting',
                'websiteUrl' => 'websiteUrlTesting',
                'id' => 'idTesting123',
                'contactEmail' => 'contactEmail@testing.com',
                'contactPhoneNumber' => '30022211111',
            ],
            'merchantCategoryCode' => 'testingMMC',
            'links' => [
                'viewOrderUrl' => 'testingUrlViewOrderUrl',
                'requestRefundUrl' => 'testingUrlRequestRefundUrl',
                'buyAgainUrl' => 'testingUrlBuyAgainUrl',
                'writeReviewUrl' => 'testingUrlWriteReviewUrl',
            ],
            'advertising' => [
                'channel' => 'website',
                'affiliate' => 'testingAffiliate',
                'subAffiliate' => 'testingSubAffiliate',
                'writeReviewUrl' => 'testingWritingUrlReview',
                'events' => [
                    [
                        'type' => 'event1Type',
                        'value' => 'event1TValue',
                    ],
                    [
                        'type' => 'event2Type',
                        'value' => 'event2TValue',
                    ],
                ],
                'campaign' => [
                    'id' => 'campaignId',
                    'name' => 'campaignName',
                ],
            ],
        ], $orderRequest->body());

        $clean = $this->getOrderRequestStructure();
        unset($clean['promotions']);
        unset($clean['loyalty']);
        unset($clean['account']);
        unset($clean['payment']['items']);
        unset($clean['additional']);
        unset($clean['links']);
        unset($clean['merchant']);
        unset($clean['advertising']);

        $clean = new CreateOrder($clean);

        $this->assertArrayNotHasKey('promotions', $clean->body());
        $this->assertArrayNotHasKey('items', $clean->body());
        $this->assertArrayNotHasKey('customFields', $clean->body());
        $this->assertArrayNotHasKey('loyalty', $clean->body());
        $this->assertArrayNotHasKey('links', $clean->body());
        $this->assertArrayNotHasKey('merchant', $clean->body());
        $this->assertArrayNotHasKey('advertising', $clean->body());
    }
}

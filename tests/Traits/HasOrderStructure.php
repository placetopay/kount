<?php

namespace Tests\Traits;

use PlacetoPay\Kount\Constants\DecisionCodes;
use PlacetoPay\Kount\Constants\ShippingMethods;
use PlacetoPay\Kount\Constants\ShippingTypes;
use PlacetoPay\Kount\Constants\TransactionStatuses;

trait HasOrderStructure
{
    public static function behavioursProvider(): array
    {
        return [
            DecisionCodes::APPROVE . ' Order' => [DecisionCodes::APPROVE],
            DecisionCodes::REVIEW . ' Order' => [DecisionCodes::REVIEW],
            DecisionCodes::DECLINE . ' Order' => [DecisionCodes::DECLINE],
        ];
    }

    public function getOrderRequestStructure(array $overrides = []): array
    {
        return array_replace_recursive([
            'ipAddress' => '192.168.1.1',
            'date' => '2024-06-01T12:00:00.000Z', // must be in RFC3339 format and must not be in the future
            'payment' => [
                'reference' => 'ORDER123',
                'amount' => [
                    'total' => 12000,
                    'currency' => 'USD',
                    'inMinorUnit' => false,
                    'taxCountry' => 'US',
                    'taxes' => [
                        [
                            'kind' => 'iva',
                            'amount' => 5,
                        ],
                        [
                            'kind' => 'stateTax',
                            'amount' => 5,
                        ],
                    ],
                    'details' => [
                        [
                            'kind' => 'subtotal',
                            'amount' => 10000,
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
                        'desc' => 'Product 1',
                        'qty' => 1,
                        'sku' => 'SKU1',
                        'price' => 5000,
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
                        'street2' => 'Apt 4',
                        'city' => 'New York',
                        'state' => 'NY',
                        'country' => 'US',
                        'postalCode' => '10001',
                    ],
                ],
            ],

            'account' => [
                'id' => '103752',
                'type' => 'customer',
                'creationDateTime' => '2020-01-01T00:00:00.000Z', // must be in RFC3339 format and must not be in the future
                'username' => 'user1',
                'accountIsActive' => true,
            ],

            'transaction' => [
                'processor' => 'VISA',
                'processorId' => 'PROC123',
                'status' => TransactionStatuses::PENDING, // One of TransactionStatuses constants
                'authResult' => 'AUTH',
                'verification' => [
                    'cvvStatus' => '',
                    'avsStatus' => '2',
                ],
                'declineCode' => '00',
                'authorization' => 'AUTHCODE123',
                'receipt' => 'ARN123',
            ],

            'instrument' => [
                'card' => [
                    'bin' => '411111',
                    'last4' => '1111',
                    'cardBrand' => 'VISA',
                ],
                'kount' => [
                    'session' => 'SESSION123',
                ],
            ],

            'payer' => [
                'document' => '123456789',
                'documentType' => 'CC',
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
                'accessUrl' => 'www.google.com/downloadTesting',
                'digitalDownloaded' => 'false',
                'downloadDeviceIp' => '168.161.1.1',
            ],

            'store' => [
                'id' => 'STORE001',
                'name' => 'Main Store',
                'address' => [
                    'street' => '456 Store St',
                    'city' => 'New York',
                    'country' => 'US',
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
                        'percentage' => 100,
                        'currency' => 'USD',
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

            'additional' => ['customData1' => 'value1'],
        ], $overrides);
    }
}

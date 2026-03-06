# Kount SDK for RIS and Data Collector

## Installation

This SDK can be installed easily through composer

```
composer require placetopay/kount
```

## Usage

```php
$service = new \PlacetoPay\Kount\KountService([
    'merchant' => 'YOUR_MERCHANT',
    'apiKey' => 'THE_API_KEY_PROVIDED',
    'website' => 'THE_AWC_SITE_NAME',
]);
```

### Data Collector

First on the page where the credit card information will be gathered you need to place the iframe for the data collector, make sure to replace YOUR_WEBPAGE_URL, YOUR_MERCHANT and THE_SESSION for the payment

Note: It HAS to be over HTTPS, and it does NOT has to be on the root of your url, you can use https://YOUR_WEBPAGE_URL/kount/something/logo.htm, and I'm not entirely sure that it needs to call logo.htm and logo.gif, but I'm using those names anyway

```html

<iframe width=1 height=1 frameborder=0 scrolling=no
        src="https://YOUR_WEBPAGE_URL/logo.htm?m=YOUR_MERCHANT&s=THE_SESSION">
    <img width=1 height=1 src="https://YOUR_WEBPAGE_URL/logo.gif?m=YOUR_MERCHANT&s=THE_SESSION">
</iframe>
``` 

This example it's made with Laravel, but the principle it's the same, slug its the logo.htm or logo.gif part, and the session it's captured through the GET variable, the merchant it's not required because it has been set on the initialization of the service

Once this it's done, the data collector will be working just fine.

### Fraud Payment Orders [KOUNT DOCUMENTATION](https://developer.kount.com/hc/en-us/articles/14474979202068-Payments-Fraud-v2-0-Integration-Guide)

Once the card information, payer data, items and other has been captured and you have the information on your server
just make an array with the information to send to Kount in this way

```php

$request = [
    'ipAddress' => '127.0.0.1',
    'date' => '2024-06-01T12:00:00.000Z',
    'payment' => [
        'reference' => 'TESTING_REFERENCE',
        'amount' => [
            'subtotal' => 10000, // if amountInMinorUnit is true, this means 100.00
            'total' => 12000, // if amountInMinorUnit is true, this means 100.00
            'currency' => 'USD',
            'inMinorUnit' => true, // if true, ALL amounts must be sent in minor units (cents)
            'taxCountry' => 'US',
            'taxes' => [
                [
                    'kind' => 'iva',
                    'amount' => 1500,
                ],
                [
                    'kind' => 'stateTax',  // so means outOfStateTotal in payments fraud
                    'amount' => 500,
                ],
            ],
            'details' => [
                [
                    'kind' => 'subtotal',
                    'amount' => 10000,
                ],
                [
                    'kind' => 'shipping',
                    'amount' => 150000,
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
                'price' => 7000,
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
                'price' => 3000,
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
        'authResult' => 'UNKNOWN', // APPROVED, DECLINED, ERROR, UNKNOWN
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
];
```

Please try to provide as much information as you can, but there is NOT required shipping, gender, shipmentType, more than 1 item (It has to be at least one), address for payer information

```php
try {
    $tokenResponse = $kountService->token();

    if (!$tokenResponse->successful()) {
        throw new Exception('Failed to authenticate: ' . json_encode($tokenResponse->errors()));
    }

    // Inquiry  an order
    $inquiryResponse = $kountService->inquiryOrder($tokenResponse->accessToken(), $request);
    
    // For trace purposes if you want
    $kountCode = $response->transaction->id();

    // For trace purposes if you want
    $score = $response->omniscore();

    if ($response->shouldApprove()) {
        // Approve the transaction
    } 
    
    if ($response->shouldDecline()) {
        // Guess what
    } 
     
    if ($response->shouldReview()) {
        // The decision it's to review
    }
    
    
    // For Another Processes you can also
   
    // create an order
    $createdOrder = $kountService->createOrder($tokenResponse->accessToken(), $request);

    // get an order
    if ($createdOrder->successful() && !empty($createdOrder->orderId())) {
        print_r('success created order ' . $createdOrder->orderId() . PHP_EOL);
        $getOrder = $kountService->getOrder($tokenResponse->accessToken(), $createdOrder->orderId());

        print_r('success queried order ' . $createdOrder->orderId() . PHP_EOL);
    } else {
        throw new Exception('Failed to create order');
    }

    $notifyRefund = $kountService->notifyChargeback($tokenResponse->accessToken(), [
        'orderId' => $createdOrder->orderId(),
        'fraudReportType' => PlacetoPay\Kount\Constants\FraudReportTypes::OTHER, // one of FraudReportTypes constants
        'refund' => [
            'transactionId' => 'refundTransactionId123',
            'date' => '2024-06-10T12:00:00.000Z',
            'amount' => [
                'total' => 12.000,
                'currency' => 'USD',
                'inMinorUnit' => false,
            ],
            'gatewayReceipt' => 'receiptOfApprovedRefund123',
        ],
    ]);

    if ($notifyRefund->successful()) {
        print_r('success notified refund for order ' . PHP_EOL);
    }

    $notifyRefund = $kountService->notifyChargeback($tokenResponse->accessToken(), [
        'orderId' => $createdOrder->orderId(),
        'fraudReportType' => PlacetoPay\Kount\Constants\FraudReportTypes::OTHER, // one of FraudReportTypes constants
        'chargeback' => [
            'transactionId' => 'anyString',
            'reasonCode' => 'anyString',
            'cardType' => 'anyString',
        ],
    ]);

    if ($notifyRefund->successful()) {
        print_r('success notified chargeback for order ' . PHP_EOL);
    }
} catch (KountServiceException $e) {
    // Handle the error message
}
```

### Available response information

The response object provides a convenient structure and methods that allow you to get all the information returned by Kount.

```php
$response->omniscore();     //  67
$response->toArray();
/**
[
    'version' => 'v2.99.0',
    'order' => [
        'orderId' => 'XCND9N8FXLT324LZ',
        'merchantOrderId' => 'ORDER123',
        'channel' => 'default',
        'deviceSessionId' => 'SESSION123',
        'creationDateTime' => '2024-06-01T12:00:00.000Z',
        'riskInquiry' => [
            'decision' => 'APPROVE',
            'omniscore' => 63.1,
            'persona' => [
                'uniqueCards' => 29,
                'uniqueDevices' => 11,
                'uniqueEmails' => 15,
                'riskiestCountry' => 'US',
                'totalBankApprovedOrders' => 8,
                'totalBankDeclinedOrders' => 65,
                'maxVelocity' => 31,
                'riskiestRegion' => ''
            ],
            'device' => null,
            'segmentExecuted' => [
                'segment' => [
                    'id' => '3e90db0d-0490-4c95-bc5b-43658173dcb5',
                    'name' => 'Default',
                    'priority' => 1
                ],
                'policiesExecuted' => [],
                'tags' => []
            ],
            'email' => null,
            'policyManagement' => null,
            'reasonCode' => ''
        ],
        'transactions' => [
            [
                'transactionId' => 'XCND9N8FXLT324LZ#0',
                'merchantTransactionId' => 'ORDER123',
                'payment' => [
                    [
                        'cardBrand' => 'CARD'
                    ]
                ],
                'processorMerchantId' => ''
            ]
        ],
        'fulfillment' => [
            [
                'fulfillmentId' => 'XCND9N8FXLT324LZ#0',
                'merchantFulfillmentId' => 'testing1233ljh'
            ]
        ]
    ],
    'warnings' => []
];
 */
```

#### Triggered rules

```php
$response->triggeredRules();
```

#### Errors

```php
// Example of a failed response


$response->errors();
/** 
[
    'correlationId' => 'aa4706ed-aead-1ec4-9347-0c3d4d96431c',
    'error' => [
        'code' => 400,
        'message' => 'Fulfillment[0].Type: Fulfillment type must be one of IN_PERSON, , SHIPPED, DIGITAL, STORE_PICK_UP, LOCAL_DELIVERY, STORE_DRIVE_UP: failed to validate input'
    ]
];
 */


```

### Mocked responses

If you change the client on the settings for the mock client the responses would be mocked ones and the real service will not be used

```php
return new KountService([
    'client' => MockClient::client(),
    ...
]);
```

## Mock Options

After this mock instance is loaded, the available options to mock are listed below.  
These values are passed via `payment.reference`, meaning the reference on the transaction.

- **REVIEW** – Simulates a review response.
- **DECLINE** – Simulates a declination response.
- **EXCEPTION** – Simulates an internal exception.
- **VALIDATION_ERROR** – Simulates an error on the request.

Any other reference will return an **approved** response.

---

## Token Method Testing

If you want to test the **token** method, you can use the following API keys:

- `invalid_api_key_for_testing_purposes`
- `valid_api_key_for_testing_purposes`

---

## Authentication Error Simulation

If you want to simulate an **authentication error** in other methods  
(such as `createOrder`, `inquiryOrder`, etc.), you can use the following value:

- `invalid_token_for_testing_purposes`

For a correct configuration, you should send your valid API token as:

- `valid_token_for_testing_purposes`

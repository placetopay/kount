<?php

namespace PlacetoPay\Kount\Helpers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PlacetoPay\Kount\Constants\DecisionCodes;
use Psr\Http\Message\RequestInterface;

class MockClient
{
    private static $instance;

    protected RequestInterface $request;
    protected array $data = [];
    protected array $lastResponse = [];

    public const INVALID_API_TOKEN = 'invalid_token_for_testing_purposes';
    public const VALID_API_TOKEN = 'valid_token_for_testing_purposes';
    public const INVALID_API_KEY = 'invalid_api_key_for_testing_purposes';
    public const VALID_API_KEY = 'valid_api_key_for_testing_purposes';

    private function __construct()
    {
    }



    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function request(): RequestInterface
    {
        return $this->request;
    }

    public function lastResponse(): array
    {
        return $this->lastResponse;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function response($code, $body, $headers = [], $reason = null): FulfilledPromise
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        $headers = array_replace([
            'Date' => date('D, d M Y H:i:s e'),
            'Content-Type' => 'application/json',
            'Content-Length' => '68',
            'Connection' => 'keep-alive',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            'Cache-control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => 0,
            'efx-transaction-id' => 'a94cc934-0b78-3f58-ad6b-6f4e990b1845',
            'Access-Control-Allow-Origin' => '',
            'Access-Control-Allow-Headers' => '',
            'Access-Control-Max-Age' => 3628800,
            'Access-Control-Allow-Methods' => '',
            'Pragma' => 'no-cache',
        ], $headers);

        return new FulfilledPromise(
            new Response($code, $headers, $body, '1.1', $reason)
        );
    }

    /**
     * @throws Exception
     */
    public function __invoke(RequestInterface $request, array $options): FulfilledPromise
    {
        $this->request = $request;

        $uri = $request->getUri()->getPath();

        if (str_contains($uri, '/v1/token')) {
            $response = $this->handleToken();

            $this->lastResponse = $response[1];

            return $this->response(...$response);
        }

        if (!empty($body = $request->getBody()->getContents())) {
            $this->data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        }

        $method = $request->getMethod();

        if ('Bearer ' . self::VALID_API_TOKEN != $request->getHeader('Authorization')[0] ?? '') {
            $response = [401, [
                'fault' => [
                    'faultstring' => '{"efxErrorCode": "401.04", "messageParams": ["Invalid Token"]}',
                    'detail' => [
                        'errorcode' => 'custom',
                    ],
                ],
            ]];

            $this->lastResponse = $response[1];

            return $this->response(...$response);
        }

        $response = match (true) {
            $method == 'POST' && $request->getUri()->getQuery() === 'riskInquiry=true' => $this->handleInquiryOrder(),
            $method == 'POST' => $this->handleCreateOrder(),
            $method == 'GET' => $this->handleGetOrder(),
            $method == 'PATCH' && str_contains($uri, ':batchUpdateReversals') => $this->handleReversals(),
            $method == 'PATCH' => $this->handleUpdate(),
            default => ['500', [
                'fault' => [
                    'faultstring' => '{"efxErrorCode": "501.01"}',
                    'detail' => [
                        'errorcode' => 'custom',
                    ],
                ],
            ]]
        };

        $this->lastResponse = $response[1];

        return $this->response(...$response);
    }

    public function getData(string $attribute): mixed
    {
        return ArrayHelper::get($this->data(), $attribute);
    }

    public static function client(): Client
    {
        return new Client(['handler' => HandlerStack::create(
            self::instance()
        )]);
    }

    private function handleUpdate(): array
    {
        $orderId = str_replace('/commerce/v2/orders/', '', $this->request->getUri()->getPath());

        $response = [
            'orderId' => $orderId,
            'merchantOrderId' => $this->getData('merchantOrderId'),
            'channel' => 'DEFAULT',
            'deviceSessionId' => $this->getData('deviceSessionId'),
            'creationDateTime' => '2024-06-01T12:10:00.00Z',
        ];

        return match ($orderId) {
            'VALIDATION_ERROR' => [400, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 400,
                    'message' => 'failed to validate input',
                ],
            ]],
            'NOT_FOUND' =>[404, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 404,
                    'message' => 'unable to retrieve requested resource. resource does not exist',
                ],
            ]],
            'EXCEPTION' => throw new Exception('Testing purposes exception'),
            default => [200, $response]
        };
    }

    private function handleToken(): array
    {
        return match ($this->request()->getHeader('Authorization')[0] ?? '') {
            'Basic ' . self::INVALID_API_KEY => [200, [
                'token_type' => 'Bearer',
                'expires_in' => 1200,
                'access_token' => self::INVALID_API_TOKEN,
                'scope' => 'k1_integration_api',
            ]],
            'Basic ' . self::VALID_API_KEY => [200, [
                'token_type' => 'Bearer',
                'expires_in' => 1200,
                'access_token' => self::VALID_API_TOKEN,
                'scope' => 'k1_integration_api',
            ]],
            default => [401, [
                'errorCode' => 'invalid_client',
                'errorSummary' => 'Invalid value for \'client_id\' parameter.',
                'errorLink' => 'invalid_client',
                'errorId' => 'oaelIt6Eb5ZRbO9cmlJrouO0A',
                'errorCauses' => [],
            ]]
        };
    }

    private function basicOrderResponse(): array
    {
        $id = strtoupper(uniqid());

        $transaction = $this->getData('order.transactions')[0] ?? [];
        $fulfillment = $this->getData('order.fulfillment')[0] ?? [];

        return [
            'version' => 'v2.92.0',
            'order' => [
                'orderId' => $id,
                'merchantOrderId' => $this->getData('merchantOrderId'),
                'channel' => $this->getData('channel') ?? '',
                'deviceSessionId' => $this->getData('deviceSessionId'),
                'creationDateTime' => $this->getData('creationDateTime'),
                'riskInquiry' => null,
                'transactions' => [
                    [
                        'transactionId' => $id . '#0',
                        'merchantTransactionId' => $transaction['merchantTransactionId'] ?? '',
                        'payment' => [
                            [
                                'cardBrand' => $transaction['payment']['cardBrand'] ?? '',
                            ],
                        ],
                        'processorMerchantId' => $transaction['processorMerchantId'] ?? '',
                    ],
                ],
                'fulfillment' => [
                    [
                        'fulfillmentId' => $id . '#0',
                        'merchantFulfillmentId' => $fulfillment['merchantFulfillmentId'] ?? '',
                    ],
                ],
            ],
            'warnings' => [],
        ];
    }

    private function handleCreateOrder(): array
    {
        $response = $this->basicOrderResponse();

        return match ($this->getData('merchantOrderId')) {
            'VALIDATION_ERROR' => [400, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 400,
                    'message' => 'merchantOrderId: field must be valid: failed to validate input',
                ],
            ]],
            'EXCEPTION' => throw new Exception('Testing purposes exception'),
            default => [200, $response]
        };
    }

    private function handleInquiryOrder(): array
    {
        $response = $this->basicOrderResponse();

        $decision = match ($this->getData('merchantOrderId')) {
            DecisionCodes::REVIEW => DecisionCodes::REVIEW,
            DecisionCodes::DECLINE => DecisionCodes::DECLINE,
            default => DecisionCodes::APPROVE,
        };

        $omniscore = match ($this->getData('merchantOrderId')) {
            DecisionCodes::REVIEW => 50.0,
            DecisionCodes::DECLINE => 10.0,
            default => 99.9,
        };

        $response = array_merge_recursive($response, [
            'order' => [
                'riskInquiry' => [
                    'decision' => $decision,
                    'omniscore' => $omniscore,
                    'persona' => [
                        'uniqueCards' => 3,
                        'uniqueDevices' => 5,
                        'uniqueEmails' => 2,
                        'riskiestCountry' => 'US',
                        'totalBankApprovedOrders' => 3,
                        'totalBankDeclinedOrders' => 4,
                        'maxVelocity' => 2,
                        'riskiestRegion' => 'US-ID',
                    ],
                    'device' => [
                        'id' => '7363b8ae6b2247b99f5d56fc81102254',
                        'collectionDateTime' => '2021-03-02T19:01:37Z',
                        'browser' => 'Chrome 106.0.0.0',
                        'deviceAttributes' => [
                            'os' => 'Android 9.0.0',
                            'firstSeenDateTime' => '2021-03-02T19:01:37Z',
                            'language' => 'en-US',
                            'timezoneOffset' => 60,
                            'mobileSdkType' => 'Android App SDK',
                            'cookiesEnabled' => false,
                            'screenResolution' => '1080x1920',
                            'ip' => [
                                'address' => '127.0.0.1',
                                'organization' => 'Equifax',
                                'piercedAddress' => '192.168.0.1',
                                'piercedOrganization' => 'Equifax',
                            ],
                            'localTime' => '2021-03-02T19:01:37Z',
                            'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36',
                        ],
                        'location' => [
                            'areaCode' => '555',
                            'city' => 'Boise',
                            'country' => 'United States of America',
                            'countryCode' => 'US',
                            'latitude' => 44.8729,
                            'longitude' => -120.3456,
                            'postalCode' => '90210',
                            'region' => 'Oregon',
                            'regionCode' => 'ID',
                            'localeCountryCode' => 'US',
                        ],
                        'tor' => true,
                    ],
                    'email' => [
                        'isVerifiedDomain' => true,
                        'firstSeen' => '2023-01-15T10:30:00Z',
                        'mostRecent' => '2023-01-15T10:30:00Z',
                    ],
                    'policyManagement' => [
                        'decision' => $decision,
                        'setExecuted' => [
                            'name' => 'Black Friday',
                            'version' => '2023-01-15T10:30:00Z',
                        ],
                        'segmentExecuted' => [
                            'id' => 'f28eb8fc-3e71-41ae-a02f-a095046aa682',
                            'name' => 'Devices Outside United States',
                            'priority' => 0,
                        ],
                        'policiesExecuted' => [
                            [
                                'id' => 'f4ce5e65-a7c7-4cbb-a7bc-c104df90cbea',
                                'name' => 'High Risk Device',
                                'outcome' => $decision,
                            ],
                        ],
                        'tags' => [
                            'risky_device',
                            'unverified_location',
                        ],
                        'tagWeights' => [
                            [
                                'name' => 'risky_device',
                                'weight' => 110,
                            ],
                        ],
                    ],
                    'reasonCode' => '10.2',
                    'segmentExecuted' => [
                        'segment' => [
                            'id' => '12345678-1234-1234-1234-123456789012',
                            'name' => 'Segment for Catching Fraud',
                            'priority' => 0,
                        ],
                        'policiesExecuted' => [
                            [
                                'id' => '12345678-1234-1234-1234-123456789012',
                                'name' => 'Policy for Catching Fraud',
                                'outcome' => [
                                    'type' => 'guidance',
                                    'value' => $decision,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return match ($this->getData('merchantOrderId')) {
            'VALIDATION_ERROR' => [400, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 400,
                    'message' => 'merchantOrderId: field must be valid: failed to validate input',
                ],
            ]],
            'EXCEPTION' => throw new Exception('Testing purposes exception'),
            default => [200, $response]
        };
    }

    private function handleGetOrder(): array
    {
        $orderId = str_replace('/commerce/v2/orders/', '', $this->request->getUri()->getPath());

        $decision = match ($orderId) {
            DecisionCodes::REVIEW => DecisionCodes::REVIEW,
            DecisionCodes::DECLINE => DecisionCodes::DECLINE,
            default => DecisionCodes::APPROVE,
        };

        $omniscore = match ($orderId) {
            DecisionCodes::REVIEW => 50.0,
            DecisionCodes::DECLINE => 10.0,
            default => 99.9,
        };

        $response = [
            'version' => 0,
            'order' => [
                'orderId' => $orderId,
                'merchantOrderId' => 'd121ea2210434ffc8a90daff9cc97e76',
                'channel' => 'DEFAULT',
                'deviceSessionId' => 'd121ea2210434ffc8a90daff9cc97e76',
                'creationDateTime' => '2019-08-24T14:15:22Z',
                'userIp' => '192.168.0.1',
                'riskInquiry' => [
                    'decision' => $decision,
                    'omniscore' => $omniscore,
                    'persona' => [
                        'uniqueCards' => 0,
                        'uniqueDevices' => 0,
                        'uniqueEmails' => 0,
                        'riskiestCountry' => 'US',
                        'totalBankApprovedOrders' => 0,
                        'totalBankDeclinedOrders' => 0,
                        'maxVelocity' => 0,
                    ],
                    'device' => [
                        'id' => '7363b8ae6b2247b99f5d56fc81102254',
                        'collectionDateTime' => '2019-08-24T14:15:22Z',
                        'browser' => 'Chrome 106.0.0.0',
                        'deviceAttributes' => [
                            'os' => 'Android 9.0.0',
                            'firstSeenDateTime' => '2021-03-02T19:01:37Z',
                            'language' => 'en-US',
                            'timezoneOffset' => '60',
                            'mobileSdkType' => 'Android App SDK',
                        ],
                        'location' => [
                            'areaCode' => '555',
                            'city' => 'Boise',
                            'country' => 'United States of America',
                            'countryCode' => 'US',
                            'latitude' => 44.8729,
                            'longitude' => -120.3456,
                            'postalCode' => '90210',
                            'region' => 'Oregon',
                            'regionCode' => 'ID',
                        ],
                        'tor' => true,
                    ],
                    'emailInsights' => [
                        'isVerifiedDomain' => true,
                        'firstSeen' => '2023-01-15T10:30:00Z',
                        'mostRecent' => '2023-01-15T10:30:00Z',
                    ],
                    'policyManagement' => [
                        'decision' => $decision,
                        'set' => [
                            'name' => 'Black Friday',
                            'version' => '2023-01-15T10:30:00Z',
                        ],
                        'segment' => [
                            'id' => '12345678-1234-1234-1234-123456789012',
                            'name' => 'Devices Outside United States',
                        ],
                        'policiesExecuted' => [
                            [
                                'id' => '12345678-1234-1234-1234-123456789012',
                                'name' => 'High Risk Device',
                                'outcome' => $decision,
                            ],
                            [
                                'id' => '12345678-1234-1234-1234-123456784567',
                                'name' => 'Unverified Location',
                                'outcome' => $decision,
                            ],
                        ],
                        'tags' => [
                            'risky_device',
                            'unverified_location',
                            'device_outsideUS',
                        ],
                        'tagWeights' => [
                            [
                                'name' => 'risky_device',
                                'weight' => 110,
                            ],
                            [
                                'name' => 'unverified_location',
                                'weight' => 10,
                            ],
                        ],
                    ],
                ],
                'reversals' => [
                    'chargeback' => [
                        'isChargeback' => true,
                        'reasonCode' => '10.2',
                        'cardType' => 'visa',
                    ],
                    'refund' => [
                        'isRefund' => true,
                        'dateTime' => '2019-08-24T14:15:22Z',
                        'amount' => 100,
                        'currency' => 'USD',
                    ],
                    'fraudReportType' => 'TC40',
                ],
                'notes' => [
                    [
                        'agent' => [
                            'name' => 'Jane Doe',
                            'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                        ],
                        'note' => 'This is an automated note.',
                        'dateTime' => '2019-08-24T14:15:22Z',
                        'isAutomated' => true,
                    ],
                    [
                        'agent' => [
                            'name' => 'John Smith',
                            'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                        ],
                        'note' => 'This is a manual note.',
                        'dateTime' => '2019-08-24T14:15:22Z',
                        'isAutomated' => false,
                    ],
                ],
                'assignedAgent' => [
                    'name' => 'Jane Doe',
                    'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                ],
                'account' => [
                    'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                    'type' => 'BASIC',
                    'creationDateTime' => '2019-08-24T14:15:22Z',
                    'username' => 'jsmith1',
                    'accountIsActive' => true,
                ],
                'items' => [
                    [
                        'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                        'price' => 100,
                        'description' => 'Samsung 46\'\' LCD HDTV',
                        'name' => 'LN46B610',
                        'quantity' => 1,
                        'category' => 'TV',
                        'subCategory' => 'LCD',
                        'isDigital' => false,
                        'isService' => false,
                        'sku' => 'TSH-000-S',
                        'upc' => '03600029145',
                        'brand' => 'LG',
                        'url' => 'https://www.example.com/store/tsh-000-s',
                        'imageUrl' => 'https://www.example.com/store/tsh-000-s/thumbnail.png',
                        'physicalAttributes' => [
                            'color' => 'Midnight Purple',
                            'size' => 'XL',
                            'weight' => '5 lbs.',
                            'height' => '12 in.',
                            'width' => '6 in.',
                            'depth' => '36 cm',
                        ],
                        'descriptors' => [
                            'halloween',
                            'mask',
                        ],
                    ],
                ],
                'fulfillment' => [
                    [
                        'merchantFulfillmentId' => 'd121ea2210434ffc8a90daff9cc97e76',
                        'type' => 'SHIPPED',
                        'items' => [
                            [
                                'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                                'quantity' => 3,
                            ],
                            [
                                'id' => '23e69466888d11eda1eb0242ac120002',
                                'quantity' => 1,
                            ],
                        ],
                        'status' => 'SCHEDULED',
                        'accessUrl' => 'https://example.com/digitalgood/1213901281290',
                        'shipping' => [
                            'amount' => 893,
                            'provider' => 'FEDEX',
                            'trackingNumber' => 'TBA056059680404',
                            'method' => 'EXPRESS',
                        ],
                        'recipientPerson' => [
                            'name' => [
                                'prefix' => 'Mr.',
                                'first' => 'William',
                                'middle' => 'Sawyer',
                                'family' => 'Doe',
                                'suffix' => 'III',
                                'preferred' => 'Bill',
                            ],
                            'dateOfBirth' => '2000-10-31',
                            'govermentId' => [
                                'type' => 'DRIVERS_LICENSE_STATE_ID',
                                'value' => 'ABC999999',
                                'state' => 'US-AZ',
                            ],
                            'emailAddress' => 'john.doe@example.com',
                            'phoneNumber' => '+12081234567',
                            'address' => [
                                'line1' => '12345 MyStreet Ave',
                                'line2' => 'Suite 256',
                                'city' => 'Poplar Bluff',
                                'region' => 'CO',
                                'countryCode' => 'US',
                                'postalCode' => '63901-0000',
                            ],
                        ],
                        'store' => [
                            'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                            'name' => '10th & Main Acme Inc.',
                            'address' => [
                                'line1' => '12345 MyStreet Ave',
                                'line2' => 'Suite 256',
                                'city' => 'Poplar Bluff',
                                'region' => 'CO',
                                'countryCode' => 'US',
                                'postalCode' => '63901-0000',
                            ],
                        ],
                    ],
                ],
                'transactions' => [
                    [
                        'merchantTransactionId' => 'd121ea2210434ffc8a90daff9cc97e76',
                        'processor' => 'ADYEN',
                        'processorMerchantId' => '5206080947171696',
                        'payment' => [
                            'type' => 'CREDIT_CARD',
                            'paymentToken' => 'insertlongtokenhere',
                            'bin' => '483312',
                            'last4' => '1111',
                        ],
                        'subtotal' => 10000,
                        'orderTotal' => 100,
                        'currency' => 'USD',
                        'tax' => [
                            'isTaxable' => true,
                            'taxableCountryCode' => 'US',
                            'taxAmount' => 400,
                            'outOfStateTaxAmount' => 43,
                        ],
                        'items' => [
                            [
                                'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                                'quantity' => 3,
                            ],
                        ],
                        'billedPerson' => [
                            'name' => [
                                'prefix' => 'Mr.',
                                'first' => 'William',
                                'middle' => 'Sawyer',
                                'family' => 'Doe',
                                'suffix' => 'III',
                                'preferred' => 'Bill',
                            ],
                            'emailAddress' => 'john.doe@example.com',
                            'phoneNumber' => '+15551234567',
                            'address' => [
                                'line1' => '5813-5849 Quail Meadows Dr',
                                'line2' => 'string',
                                'city' => 'Poplar Bluff',
                                'region' => 'CO',
                                'postalCode' => '63901-0000',
                                'countryCode' => 'US',
                            ],
                            'dateOfBirth' => '2000-10-31',
                            'govermentId' => [
                                'type' => 'DRIVERS_LICENSE_STATE_ID',
                                'value' => 'ABC999999',
                                'state' => 'US-AZ',
                            ],
                        ],
                        'transactionStatus' => 'CAPTURED',
                        'authorizationStatus' => [
                            'authResult' => 'Approved',
                            'dateTime' => '2022-10-31T22:01:43Z',
                            'verificationResponse' => [
                                'isAvsStreetMatch' => true,
                                'isAvsZipMatch' => false,
                                'cvvMatch' => 'Match',
                            ],
                            'declineCode' => '01',
                            'processorAuthCode' => '741256',
                            'processorTransactionId' => 'NMI0983',
                            'acquirerReferenceNumber' => '40614857370',
                        ],
                    ],
                ],
                'promotions' => [
                    [
                        'id' => 'BOGO10',
                        'description' => 'Buy one, get one 10% off',
                        'status' => 'accepted',
                        'statusReason' => 'Promotion cannot be combined.',
                        'discount' => [
                            'percentage' => 0.1,
                            'amount' => 100,
                            'currency' => 'USD',
                        ],
                        'credit' => [
                            'creditType' => 'GIFT_CARD',
                            'amount' => 0,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                'loyalty' => [
                    'id' => 'd121ea2210434ffc8a90daff9cc97e76',
                    'description' => 'Pizza Points',
                    'credit' => [
                        'creditType' => 'PIZZA_POINTS',
                        'amount' => 150,
                        'currency' => 'USD',
                    ],
                ],
                'customFields' => [
                    'keyNumber' => 42,
                    'keyBoolean' => true,
                    'keyString' => 'value',
                    'keyDate' => '2023-03-30T15:41:58Z',
                ],
            ],
        ];

        return match ($orderId) {
            'VALIDATION_ERROR' => [400, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 400,
                    'message' => 'failed to validate input',
                ],
            ]],
            'NOT_FOUND' => [404, [
                'correlationId' => strtoupper(uniqid()),
                'error' => [
                    'code' => 404,
                    'message' => 'unable to retrieve requested resource. resource does not exist',
                ],
            ]],
            'EXCEPTION' => throw new Exception('Testing purposes exception'),
            default => [200, $response]
        };
    }

    private function handleReversals(): array
    {
        return match ($this->getData('reversalsUpdates')[0]['orderId'] ?? '') {
            'VALIDATION_ERROR' => [200, [
                'errors' => [
                    [
                        'orderId' => 'D2WFWRV4DYS9Ms4GK',
                        'error' => 'failed to find current reversal status for order',
                    ],
                ],
            ]],
            'EXCEPTION' => throw new Exception('Testing purposes exception'),
            default => [200, [
                'errors' => [],
            ]]
        };
    }
}

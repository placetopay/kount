<?php

namespace Tests\Parsings;

use PlacetoPay\Kount\KountService;
use Tests\BaseTestCase;

class ParsingTest extends BaseTestCase
{
    protected $service;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->service = new KountService([
            'merchant' => '201000',
            'apiKey' => 'TESTING',
            'website' => 'DEFAULT',
        ]);

    }

    public function basicRequestData(array $overrides = []): array
    {
        $data = \array_replace_recursive([
            'mack' => 'Y',
            'payment' => [
                'reference' => 'TEST_20170601_201117',
                'description' => 'A numquam dolores et occaecati eum dolore.',
                'amount' => [
                    'currency' => 'COP',
                    'total' => 134000,
                ],
                'items' => [
                    [
                        'sku' => 1234,
                        'name' => 'Testing Required Product',
                        'price' => 134000,
                        'qty' => 1,
                    ],
                ],
                'allowPartial' => false,
            ],
            // Card Related
            'cardNumber' => '36545400000008',
            // M match, N Not match, X unavailable
            'cvvStatus' => 'X',
            'cardExpiration' => '12/20',
            // Person related
            'payer' => [
                'document' => '1040035000',
                'documentType' => 'CC',
                'name' => 'Stanton',
                'surname' => 'Gerhold',
                'email' => 'dcallem88@msn.com',
                'mobile' => '3006108300',
            ],
            'ipAddress' => '127.0.0.1',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
        ], $overrides);

        return $data;
    }

    public function testItParsesTheInquiryRequestInformationCorrectly()
    {
        $data = $this->basicRequestData([
            'payer' => [
                'address' => [
                    'street' => 'Fake street 123',
                    'city' => 'Medellin',
                    'state' => 'Antioquia',
                    'postalCode' => '050012',
                    'country' => 'CO',
                    'phone' => '4442310',
                ],
            ],
            'payment' => [
                'amount' => [
                    'total' => 12300,
                ],
                'items' => [
                    [
                        'sku' => '111',
                        'name' => 'TV Sony 32',
                        'category' => 'physical',
                        'qty' => '1',
                        'price' => '2340',
                        'tax' => '300',
                    ],
                    [
                        'sku' => '234',
                        'name' => 'Wireless Mouse',
                        'category' => 'physical',
                        'qty' => '2',
                        'price' => '543',
                        'tax' => '56',
                    ],
                ],
                'shipping' => [
                    'name' => 'Diego',
                    'surname' => 'Calle',
                    'email' => 'dnetix@gmail.com',
                    'address' => [
                        'street' => 'Fake street 321',
                        'city' => 'Sabaneta',
                        'state' => 'Antioquia',
                        'postalCode' => '050013',
                        'country' => 'CO',
                        'phone' => '4442310',
                    ],
                ],
            ],
            'cardNumber' => '4111111111111111',
            'gender' => 'M',
            'additional' => [
                'key_1' => 'Some Value 1',
            ],
            'shipmentType' => \PlacetoPay\Kount\Messages\Request::SHIP_SAME,
        ]);

        $inquiryRequest = $this->service->parseInquiryRequest('123', $data);

        $requestData = [
            'VERS' => '0630',
            'MODE' => 'Q',
            'SDK' => 'PHP',
            'SDK_VERSION' => 'PlacetoPay-0.0.1',

            'MERC' => '201000',
            'SESS' => '123',
            'ORDR' => $data['payment']['reference'],
            'CURR' => $data['payment']['amount']['currency'],
            'TOTL' => 1230000,
            'MACK' => $data['mack'],

            'PTYP' => 'CARD',
            'LAST4' => substr($data['cardNumber'], -4),
            'PTOK' => '411111XXXXXX1111',
            'CVVR' => $data['cvvStatus'],
            'CCMM' => '12',
            'CCYY' => '2020',

            'UNIQ' => $data['payer']['documentType'] . $data['payer']['document'],

            'NAME' => $data['payer']['name'] . ' ' . $data['payer']['surname'],
            'GENDER' => $data['gender'],
            'EMAL' => $data['payer']['email'],
            'B2A1' => $data['payer']['address']['street'],
            'B2CI' => $data['payer']['address']['city'],
            'B2ST' => $data['payer']['address']['state'],
            'B2PC' => $data['payer']['address']['postalCode'],
            'B2CC' => $data['payer']['address']['country'],
            'B2PN' => $data['payer']['address']['phone'],

            'S2NM' => $data['payment']['shipping']['name'] . ' ' . $data['payment']['shipping']['surname'],
            'S2EM' => $data['payment']['shipping']['email'],
            'S2A1' => $data['payment']['shipping']['address']['street'],
            'S2CI' => $data['payment']['shipping']['address']['city'],
            'S2ST' => $data['payment']['shipping']['address']['state'],
            'S2PC' => $data['payment']['shipping']['address']['postalCode'],
            'S2CC' => $data['payment']['shipping']['address']['country'],
            'S2PN' => $data['payment']['shipping']['address']['phone'],

            'UDF[KEY_1]' => $data['additional']['key_1'],

            'PROD_TYPE[0]' => $data['payment']['items'][0]['sku'],
            'PROD_ITEM[0]' => $data['payment']['items'][0]['name'],
            'PROD_QUANT[0]' => $data['payment']['items'][0]['qty'],
            'PROD_PRICE[0]' => 234000,
            'PROD_TYPE[1]' => $data['payment']['items'][1]['sku'],
            'PROD_ITEM[1]' => $data['payment']['items'][1]['name'],
            'PROD_QUANT[1]' => $data['payment']['items'][1]['qty'],
            'PROD_PRICE[1]' => 54300,

            'IPAD' => $data['ipAddress'],
            'UAGT' => $data['userAgent'],
            'SITE' => 'DEFAULT',
            'PENC' => 'MASK',
        ];

        $this->assertEquals($requestData, $inquiryRequest->asRequestData(), 'Parses the inquiry data correctly');
        $this->assertEquals([
            'X-Kount-Api-Key' => 'TESTING',
        ], $inquiryRequest->asRequestHeaders(), 'Parses the inquiry headers correctly');
    }

    public function testItParsesAShortRequest()
    {
        $data = $this->basicRequestData();

        $inquiryRequest = $this->service->parseInquiryRequest(4, $data);

        $requestData = [
            'VERS' => '0630',
            'MODE' => 'Q',
            'SDK' => 'PHP',
            'SDK_VERSION' => 'PlacetoPay-0.0.1',

            'MACK' => 'Y',
            'MERC' => '201000',
            'SESS' => 4,
            'ORDR' => $data['payment']['reference'],
            'CURR' => $data['payment']['amount']['currency'],
            'TOTL' => 13400000,

            'PTYP' => 'CARD',
            'LAST4' => substr($data['cardNumber'], -4),
            'PTOK' => '365454XXXXX0008',
            'CVVR' => $data['cvvStatus'],
            'CCMM' => '12',
            'CCYY' => '2020',
            'PENC' => 'MASK',

            'UNIQ' => $data['payer']['documentType'] . $data['payer']['document'],

            'NAME' => $data['payer']['name'] . ' ' . $data['payer']['surname'],
            'EMAL' => $data['payer']['email'],
            'B2PN' => $data['payer']['mobile'],
            'PROD_TYPE[0]' => $data['payment']['items'][0]['sku'],
            'PROD_ITEM[0]' => $data['payment']['items'][0]['name'],
            'PROD_QUANT[0]' => $data['payment']['items'][0]['qty'],
            'PROD_PRICE[0]' => 13400000,

            'IPAD' => $data['ipAddress'],
            'UAGT' => $data['userAgent'],
            'SITE' => 'DEFAULT',
        ];

        $this->assertEquals($requestData, $inquiryRequest->asRequestData(), 'Parses the inquiry data correctly');
        $this->assertEquals([
            'X-Kount-Api-Key' => 'TESTING',
        ], $inquiryRequest->asRequestHeaders(), 'Parses the inquiry headers correctly');
    }

    public function testItParsesCorrectlyAnotherAmounts()
    {
        $data = $this->basicRequestData(['payment' => [
            'amount' => [
                'total' => 1900,
                'currency' => 'CLP'
            ]]
        ]);
        $inquiryRequest = $this->service->parseInquiryRequest(5, $data)->asRequestData();
        $this->assertEquals(1900, $inquiryRequest['TOTL']);

        $data = $this->basicRequestData(['payment' => [
            'amount' => [
                'total' => 1900,
                'currency' => 'JOD'
            ]]
        ]);
        $inquiryRequest = $this->service->parseInquiryRequest(5, $data)->asRequestData();
        $this->assertEquals(1900000, $inquiryRequest['TOTL']);

        $data = $this->basicRequestData(['payment' => [
            'amount' => [
                'total' => 1900,
                'currency' => 'COP'
            ]]
        ]);
        $inquiryRequest = $this->service->parseInquiryRequest(5, $data)->asRequestData();
        $this->assertEquals(190000, $inquiryRequest['TOTL']);
    }
    
    public function testItParsesTheInquiryRequestWithoutPhone(): void
    {
        $data = $this->basicRequestData([
            'payer' => [
                'address' => [
                    'street' => 'Fake street 123',
                    'city' => 'Medellin',
                    'state' => 'Antioquia',
                    'postalCode' => '050012',
                    'country' => 'CO',
                ],
            ],
            'payment' => [
                'amount' => [
                    'total' => 12300,
                ],
                'items' => [
                    [
                        'sku' => '111',
                        'name' => 'TV Sony 32',
                        'category' => 'physical',
                        'qty' => '1',
                        'price' => '2340',
                        'tax' => '300',
                    ],
                    [
                        'sku' => '234',
                        'name' => 'Wireless Mouse',
                        'category' => 'physical',
                        'qty' => '2',
                        'price' => '543',
                        'tax' => '56',
                    ],
                ],
                'shipping' => [
                    'name' => 'Diego',
                    'surname' => 'Calle',
                    'email' => 'dnetix@gmail.com',
                    'address' => [
                        'street' => 'Fake street 321',
                        'city' => 'Sabaneta',
                        'state' => 'Antioquia',
                        'postalCode' => '050013',
                        'country' => 'CO',
                        'phone' => '4442310',
                    ],
                ],
            ],
            'cardNumber' => '4111111111111111',
            'gender' => 'M',
            'additional' => [
                'key_1' => 'Some Value 1',
            ],
            'shipmentType' => \PlacetoPay\Kount\Messages\Request::SHIP_SAME,
        ]);

        $inquiryRequest = $this->service->parseInquiryRequest('123', $data);

        $requestData = [
            'VERS' => '0630',
            'MODE' => 'Q',
            'SDK' => 'PHP',
            'SDK_VERSION' => 'PlacetoPay-0.0.1',

            'MERC' => '201000',
            'SESS' => '123',
            'ORDR' => $data['payment']['reference'],
            'CURR' => $data['payment']['amount']['currency'],
            'TOTL' => 1230000,
            'MACK' => $data['mack'],

            'PTYP' => 'CARD',
            'LAST4' => substr($data['cardNumber'], -4),
            'PTOK' => '411111XXXXXX1111',
            'CVVR' => $data['cvvStatus'],
            'CCMM' => '12',
            'CCYY' => '2020',

            'UNIQ' => $data['payer']['documentType'] . $data['payer']['document'],

            'NAME' => $data['payer']['name'] . ' ' . $data['payer']['surname'],
            'GENDER' => $data['gender'],
            'EMAL' => $data['payer']['email'],
            'B2A1' => $data['payer']['address']['street'],
            'B2CI' => $data['payer']['address']['city'],
            'B2ST' => $data['payer']['address']['state'],
            'B2PC' => $data['payer']['address']['postalCode'],
            'B2CC' => $data['payer']['address']['country'],
            'B2PN' => $data['payer']['mobile'],

            'S2NM' => $data['payment']['shipping']['name'] . ' ' . $data['payment']['shipping']['surname'],
            'S2EM' => $data['payment']['shipping']['email'],
            'S2A1' => $data['payment']['shipping']['address']['street'],
            'S2CI' => $data['payment']['shipping']['address']['city'],
            'S2ST' => $data['payment']['shipping']['address']['state'],
            'S2PC' => $data['payment']['shipping']['address']['postalCode'],
            'S2CC' => $data['payment']['shipping']['address']['country'],
            'S2PN' => $data['payment']['shipping']['address']['phone'],

            'UDF[KEY_1]' => $data['additional']['key_1'],

            'PROD_TYPE[0]' => $data['payment']['items'][0]['sku'],
            'PROD_ITEM[0]' => $data['payment']['items'][0]['name'],
            'PROD_QUANT[0]' => $data['payment']['items'][0]['qty'],
            'PROD_PRICE[0]' => 234000,
            'PROD_TYPE[1]' => $data['payment']['items'][1]['sku'],
            'PROD_ITEM[1]' => $data['payment']['items'][1]['name'],
            'PROD_QUANT[1]' => $data['payment']['items'][1]['qty'],
            'PROD_PRICE[1]' => 54300,

            'IPAD' => $data['ipAddress'],
            'UAGT' => $data['userAgent'],
            'SITE' => 'DEFAULT',
            'PENC' => 'MASK',
        ];

        $this->assertEquals($requestData, $inquiryRequest->asRequestData(), 'Parses the inquiry data correctly');
        $this->assertEquals([
            'X-Kount-Api-Key' => 'TESTING',
        ], $inquiryRequest->asRequestHeaders(), 'Parses the inquiry headers correctly');
    }

    public function testItParsesTheInquiryRequestWithoutPhoneOrMobile(): void
    {
        $data = $this->basicRequestData([
            'payer' => [
                'address' => [
                    'street' => 'Fake street 123',
                    'city' => 'Medellin',
                    'state' => 'Antioquia',
                    'postalCode' => '050012',
                    'country' => 'CO',
                ],
            ],
            'payment' => [
                'amount' => [
                    'total' => 12300,
                ],
                'items' => [
                    [
                        'sku' => '111',
                        'name' => 'TV Sony 32',
                        'category' => 'physical',
                        'qty' => '1',
                        'price' => '2340',
                        'tax' => '300',
                    ],
                    [
                        'sku' => '234',
                        'name' => 'Wireless Mouse',
                        'category' => 'physical',
                        'qty' => '2',
                        'price' => '543',
                        'tax' => '56',
                    ],
                ],
                'shipping' => [
                    'name' => 'Diego',
                    'surname' => 'Calle',
                    'email' => 'dnetix@gmail.com',
                    'address' => [
                        'street' => 'Fake street 321',
                        'city' => 'Sabaneta',
                        'state' => 'Antioquia',
                        'postalCode' => '050013',
                        'country' => 'CO',
                        'phone' => '4442310',
                    ],
                ],
            ],
            'cardNumber' => '4111111111111111',
            'gender' => 'M',
            'additional' => [
                'key_1' => 'Some Value 1',
            ],
            'shipmentType' => \PlacetoPay\Kount\Messages\Request::SHIP_SAME,
        ]);

        unset($data['payer']['mobile']);

        $inquiryRequest = $this->service->parseInquiryRequest('123', $data);

        $requestData = [
            'VERS' => '0630',
            'MODE' => 'Q',
            'SDK' => 'PHP',
            'SDK_VERSION' => 'PlacetoPay-0.0.1',

            'MERC' => '201000',
            'SESS' => '123',
            'ORDR' => $data['payment']['reference'],
            'CURR' => $data['payment']['amount']['currency'],
            'TOTL' => 1230000,
            'MACK' => $data['mack'],

            'PTYP' => 'CARD',
            'LAST4' => substr($data['cardNumber'], -4),
            'PTOK' => '411111XXXXXX1111',
            'CVVR' => $data['cvvStatus'],
            'CCMM' => '12',
            'CCYY' => '2020',

            'UNIQ' => $data['payer']['documentType'] . $data['payer']['document'],

            'NAME' => $data['payer']['name'] . ' ' . $data['payer']['surname'],
            'GENDER' => $data['gender'],
            'EMAL' => $data['payer']['email'],
            'B2A1' => $data['payer']['address']['street'],
            'B2CI' => $data['payer']['address']['city'],
            'B2ST' => $data['payer']['address']['state'],
            'B2PC' => $data['payer']['address']['postalCode'],
            'B2CC' => $data['payer']['address']['country'],
            'B2PN' => null,

            'S2NM' => $data['payment']['shipping']['name'] . ' ' . $data['payment']['shipping']['surname'],
            'S2EM' => $data['payment']['shipping']['email'],
            'S2A1' => $data['payment']['shipping']['address']['street'],
            'S2CI' => $data['payment']['shipping']['address']['city'],
            'S2ST' => $data['payment']['shipping']['address']['state'],
            'S2PC' => $data['payment']['shipping']['address']['postalCode'],
            'S2CC' => $data['payment']['shipping']['address']['country'],
            'S2PN' => $data['payment']['shipping']['address']['phone'],

            'UDF[KEY_1]' => $data['additional']['key_1'],

            'PROD_TYPE[0]' => $data['payment']['items'][0]['sku'],
            'PROD_ITEM[0]' => $data['payment']['items'][0]['name'],
            'PROD_QUANT[0]' => $data['payment']['items'][0]['qty'],
            'PROD_PRICE[0]' => 234000,
            'PROD_TYPE[1]' => $data['payment']['items'][1]['sku'],
            'PROD_ITEM[1]' => $data['payment']['items'][1]['name'],
            'PROD_QUANT[1]' => $data['payment']['items'][1]['qty'],
            'PROD_PRICE[1]' => 54300,

            'IPAD' => $data['ipAddress'],
            'UAGT' => $data['userAgent'],
            'SITE' => 'DEFAULT',
            'PENC' => 'MASK',
        ];

        $this->assertEquals($requestData, $inquiryRequest->asRequestData(), 'Parses the inquiry data correctly');
        $this->assertEquals([
            'X-Kount-Api-Key' => 'TESTING',
        ], $inquiryRequest->asRequestHeaders(), 'Parses the inquiry headers correctly');
    }
}

<?php


class ParsingTest extends BaseTestCase
{

    public function testItParsesTheInquiryRequestInformationCorrectly()
    {
        $service = new \PlacetoPay\Kount\KountService([
            'merchant' => '201000',
            'apiKey' => 'TESTING',
            'website' => 'DEFAULT',
        ]);

        $data = [
            'payment' => [
                'reference' => '1234',
                'amount' => [
                    'currency' => 'COP',
                    'total' => '12300',
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
                    ]
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
            // Merchant Acknowledgement
            'mack' => 'Y',
            // Card Related
            'cardNumber' => '4111111111111111',
            // M match, N Not match, X unavailable
            'cvvStatus' => 'X',
            'cardExpiration' => '12/20',
            // Person related
            'payer' => [
                'name' => 'Diego',
                'surname' => 'Calle',
                'email' => 'dnetix@gmail.com',
                'document' => '1040035000',
                'documentType' => 'CC',
                'address' => [
                    'street' => 'Fake street 123',
                    'city' => 'Medellin',
                    'state' => 'Antioquia',
                    'postalCode' => '050012',
                    'country' => 'CO',
                    'phone' => '4442310',
                ],
            ],
            'gender' => 'M',
            // Additional
            'additional' => [
                'key_1' => 'Some Value 1',
            ],
            'ipAddress' => '127.0.0.1',
            'userAgent' => 'Chrome XYZ',
            // To organize
            'shipmentType' => \PlacetoPay\Kount\Messages\Request::SHIP_SAME,
        ];

        $inquiryRequest = $service->parseInquiryRequest('123', $data);

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
            'PTOK' => $data['cardNumber'],
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
            'PENC' => 'MASK'
        ];

        $this->assertEquals($requestData, $inquiryRequest->asRequestData(), 'Parses the inquiry data correctly');
        $this->assertEquals([
            'X-Kount-Api-Key' => 'TESTING',
        ], $inquiryRequest->asRequestHeaders(), 'Parses the inquiry headers correctly');
    }

    public function testItParsesAShortRequest()
    {
        $service = new \PlacetoPay\Kount\KountService([
            'merchant' => '201000',
            'apiKey' => 'TESTING',
            'website' => 'DEFAULT',
        ]);

        $data = [
            'mack' => 'Y',
            'payment' => [
                'reference' => 'TEST_20170601_201117',
                'description' => 'A numquam dolores et occaecati eum dolore.',
                'amount' => [
                    'currency' => 'COP',
                    'total' => 134000
                ],
                'items' => [
                    [
                        'sku' => 1234,
                        'name' => 'Testing Required Product',
                        'price' => 134000,
                        'qty' => 1,
                    ]
                ],
                'allowPartial' => false
            ],
            // Card Related
            'cardNumber' => '4111111111111111',
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
                'mobile' => '3006108300'
            ],
            'ipAddress' => '127.0.0.1',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
        ];

        $inquiryRequest = $service->parseInquiryRequest(4, $data);

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
            'PTOK' => $data['cardNumber'],
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

}
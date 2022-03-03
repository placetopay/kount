<?php

namespace Tests\Features;

use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\Response;
use Tests\BaseTestCase;

class RisInquiryTest extends BaseTestCase
{
    public function basicRequest(string $session, array $overrides = []): Response
    {
        $request = array_replace([
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
                    ],
                ],
                'shipping' => [
                    'name' => 'Diego',
                    'surname' => 'Calle',
                    'email' => 'fake@email.com',
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
            // MM/YY format
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
        ], $overrides);

        return $this->service()->inquiry($session, $request);
    }

    /**
     * @test
     */
    public function it_handles_a_basic_request()
    {
        $this->expectException(KountServiceException::class);
        $this->expectExceptionMessage('501');
        $this->basicRequest('AUTH_ERR');
    }
}

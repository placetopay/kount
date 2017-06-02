# Kount SDK for RIS and Data Collector

## Installation

This SDK can be installed easily through composer
```
composer require dnetix/kount
```

## Usage

```
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
<iframe width=1 height=1 frameborder=0 scrolling=no src="https://YOUR_WEBPAGE_URL/logo.htm?m=YOUR_MERCHANT&s=THE_SESSION">
    <img width=1 height=1 src="https://YOUR_WEBPAGE_URL/logo.gif?m=YOUR_MERCHANT&s=THE_SESSION">
</iframe>
```

Then make sure that your application responds with a HTTP code 302 to redirect to the Kount's url

```php
Route::get('/kount/{slug?}', function($slug = null) {
    $s = Request::get('s');
    return redirect($service->dataCollectorUrl($s, $slug));
});
```

This example it's made with Laravel, but the principle it's the same, slug its the logo.htm or logo.gif part, and the session it's captured through the GET variable, the merchant it's not required because it has been set on the initialization of the service

Once this it's done, the data collector will be working just fine.

### RIS Inquiry

Once the card information, payer data, items and other has been captured and you have the information on your server
just make an array with the information to send to Kount in this way

```
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
];
```
Please try to provide as much information as you can, but there is NOT required shipping, gender, shipmentType, more than 1 item (It has to be at least one), address for payer information

```
$response = $service->inquiry(THE_SESSION, $data);
```
Please refer to the InquiryResponse and Response class to see all the available methods but the main ones are

```
if ($response->isSuccessful()){
    // For trace purposes if you want
    $kountCode = $response->kountCode();
    // For trace purposes if you want
    $score = $response->score();
    if ($response->shouldApprove()) {
        // Approve the transaction
    } else if ($response->shouldDecline()) {
        // Guess what
    } else {
        // The decision it's to review
    }
} else {
    // There was a problem with the connection or the request log the error and review it
    $error = $response->errorKey();
    // This one is an array with all the errors from Kount
    $errors = $response->errors();
}
```
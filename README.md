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

```php
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

```php
try {
    $response = $service->inquiry(THE_SESSION, $data);

    // For trace purposes if you want
    $kountCode = $response->transaction->id();

    // For trace purposes if you want
    $score = $response->score();

    if ($response->decision->shouldApprove()) {
        // Approve the transaction
    } 
    
    if ($response->decision->shouldDecline()) {
        // Guess what
    } 
     
    if ($response->decision->shouldReview()) {
        // The decision it's to review
    }
} catch (KountServiceException $e) {
    // Handle the error message
}
```

### Available response information

The response object provides a convenient structure and methods that allow you to get all the information returned by Kount.

```php
$response->score();         //  33
$response->omniscore();     //  67
$response->toArray();
/**
    [
        'score' => 33,
        'omniscore' => 67,
        'system' => [
            'version' => '0720',
            'mode' => 'Q',
            'merchantId' => '201000',
            'sessonId' => '3',
            'orderReference' => '1234',
        ],
        'decision' => [
            'code' => 'D',
            'description' => 'DECLINE',
            'shouldApprove' => false,
            'shouldDecline' => true,
            'shouldReview' => false,
        ],
        'verification' => [
            'geolocationCountry' => 'US',
            'geolocationRegion' => '',
            'cardBrand' => 'VISA',
            'cardIsBlacklisted' => false,
            'aCatchVerificationHasBeenPerformed' => true,
            'threeDsMerchantResponse' => '',
            'denialReasonCode' => '',
        ],
        'ip' => [
            'address' => '181.128.85.221',
            'latitude' => '6.2518',
            'longitude' => '-75.5636',
            'country' => 'CO',
            'state' => 'Antioquia',
            'city' => 'Medellín',
            'provider' => 'UNE',
        ],
        'transaction' => [
            'id' => 'P01J0KZN329Z',
            'usedCardsCount' => 1,
            'usedDevicesCount' => 1,
            'deviceLayers' => '81BBF7770C..D92909FF92.1867A9B2CB.D6112C09F7',
            'usedEmailsCount' => 1,
            'velocity' => 0,
            'maxAllowedVelocity' => 0,
            'site' => 'DEFAULT',
            'fingerprint' => '4C2410BA22A64E21BF0C73EA88E48D7E',
            'timezone' => '300',
            'localtime' => '2017-05-31 00:19',
            'region' => 'CO_02',
            'country' => 'CO',
            'httpCountry' => 'US',
            'hasProxy' => false,
            'hasJavascript' => true,
            'hasFlash' => false,
            'hasCookies' => true,
            'language' => 'en',
            'processedFromMobileDevice' => false,
            'mobileType' => '',
            'mobileIsThroughMobileForwarder' => false,
            'processedFromVoiceDevice' => false,
            'processedFromRemotePC' => false,
        ],
        'additional' => [
            'dateSinceFirstMadeTransaction' => '2017-05-30',
            'screenResolution' => '768x1366',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            'operativeSystem' => 'Linux',
            'browser' => 'Chrome 58.0.3029.110',
            'wasPreviouslyWhitelisted' => false,
        ],
        'triggeredRules' =>  [
            '729832' => 'Billing Country is not BIN Country',
            '729852' => 'Decline Network Chargebacks >1',
            '729854' => 'Review Non-Normal Network Type',
            '729858' => 'Device Location Lower Risk Review Countries',
            '729872' => 'Card on Network Chargeback List >0',
        ],
        'triggeredCounters' => [
            'CONTRACARGOSREPORTADOS' => '1',
            'HISTORICONEGATIVO' => '1',
        ],
        'warnings' => [
            'THIS IS THE FIRST WARNING',
            'THIS IS THE SECOND WARNING',
        ],
        'errorsBag' => [
            'isErrorResponse' => false,
            'code' => null,
            'key' => null,
            'errors' => [],
        ],
    ]
 */
```

#### Identifiers and references

```php
$response->system->version();          //  '0720'
$response->system->mode();             //  'Q'
$response->system->merchantId();       //  '201000'
$response->system->sessionId();        //  '3'
$response->system->orderReference();   //  '1234'
$response->system->toArray();
/**
    [
        'version' => '0720',
        'mode' => 'Q',
        'merchantId' => '201000',
        'sessonId' => '3',
        'orderReference' => '1234',
    ]
 */
```

#### Decision information
```php
$response->decision->code();             //  'D'
$response->decision->description();      //  'DECLINE'
$response->decision->shouldApprove();    //  false
$response->decision->shouldDecline();    //  true
$response->decision->shouldReview();     //  false
$response->decision->toArray();
/**
    [
        'code' => 'D',
        'description' => 'DECLINE',
        'shouldApprove' => false,
        'shouldDecline' => true,
        'shouldReview' => false,
    ]
 */
```

#### Verification result
```php
$response->verification->geolocationCountry();                     //  'US'
$response->verification->geolocationRegion();                      //  'EAST'
$response->verification->cardBrand();                              //  'VISA'
$response->verification->cardIsBlacklisted();                      //  false
$response->verification->aCatchVerificationHasBeenPerformed();     //  true
$response->verification->threeDsMerchantResponse();                //  ''
$response->verification->denialReasonCode();                       //  ''
$response->verification->toArray();
/**
    [
        'geolocationCountry' => 'US',
        'geolocationRegion' => 'EAST',
        'cardBrand' => 'VISA',
        'cardIsBlacklisted' => false,
        'aCatchVerificationHasBeenPerformed' => true,
        'threeDsMerchantResponse' => '',
        'denialReasonCode' => '',
    ]
 */
```

#### Transaction information
```php
$response->transaction->id();                                // 'P01J0KZN329Z'
$response->transaction->usedCardsCount();                    // 1
$response->transaction->usedDevicesCount();                  // 1
$response->transaction->deviceLayers();                      // '81BBF7770C..D92909FF92.1867A9B2CB.D6112C09F7'
$response->transaction->usedEmailsCount();                   // 1
$response->transaction->velocity();                          // 0
$response->transaction->maxAllowedVelocity();                // 0
$response->transaction->site();                              // 'DEFUALT
$response->transaction->fingerprint();                       // '4C2410BA22A64E21BF0C73EA88E48D7E'
$response->transaction->timezone();                          // '300
$response->transaction->localtime();                         // '2017-05-31 00:19'
$response->transaction->region();                            // 'CO_02'
$response->transaction->country();                           // 'CO'
$response->transaction->httpCountry();                       // 'US'
$response->transaction->hasProxy();                          // false
$response->transaction->hasJavascript();                     // true
$response->transaction->hasFlash();                          // false
$response->transaction->hasCookies();                        // true
$response->transaction->language();                          // 'en'
$response->transaction->processedFromMobileDevice();         // false
$response->transaction->mobileType();                        // ''
$response->transaction->mobileIsThroughMobileForwarder();    // false
$response->transaction->processedFromVoiceDevice();          // false
$response->transaction->processedFromRemotePC();             // true
$response->transaction->toArray();
/**
    [
        'id' => 'P01J0KZN329Z',
        'usedCardsCount' => 1,
        'usedDevicesCount' => 1,
        'deviceLayers' => '81BBF7770C..D92909FF92.1867A9B2CB.D6112C09F7',
        'usedEmailsCount' => 1,
        'velocity' => 0,
        'maxAllowedVelocity' => 0,
        'site' => 'DEFAULT',
        'fingerprint' => '4C2410BA22A64E21BF0C73EA88E48D7E',
        'timezone' => '300',
        'localtime' => '2017-05-31 00:19',
        'region' => 'CO_02',
        'country' => 'CO',
        'httpCountry' => 'US',
        'hasProxy' => false,
        'hasJavascript' => true,
        'hasFlash' => false,
        'hasCookies' => true,
        'language' => 'en',
        'processedFromMobileDevice' => false,
        'mobileType' => '',
        'mobileIsThroughMobileForwarder' => false,
        'processedFromVoiceDevice' => false,
        'processedFromRemotePC' => true,
    ]
 */
```


#### Transaction IP information
```php
$response->ip->address();          //  '181.128.85.221'
$response->ip->latitude();         //  '6.2518'
$response->ip->longitude();        //  '-75.5636'
$response->ip->country();          //  'CO'
$response->ip->state();            //  'Antioquia'
$response->ip->city();             //  'Medellín'
$response->ip->provider();         //  'UNE'
$response->ip->toArray();
/**
    [
        'address' => '181.128.85.221',
        'latitude' => '6.2518',
        'longitude' => '-75.5636',
        'country' => 'CO',
        'state' => 'Antioquia',
        'city' => 'Medellín',
        'provider' => 'UNE',
    ]
 */
```

#### Triggered rules

```php
$response->triggeredRules->count();     // 5
$response->triggeredRules->rules();
/**
    [
        '729832' => 'Billing Country is not BIN Country',
        '729852' => 'Decline Network Chargebacks >1',
        '729854' => 'Review Non-Normal Network Type',
        '729858' => 'Device Location Lower Risk Review Countries',
        '729872' => 'Card on Network Chargeback List >0',
    ]
 */
$response->triggeredRules->toArray();
/**
    [
        '729832' => 'Billing Country is not BIN Country',
        '729852' => 'Decline Network Chargebacks >1',
        '729854' => 'Review Non-Normal Network Type',
        '729858' => 'Device Location Lower Risk Review Countries',
        '729872' => 'Card on Network Chargeback List >0',
    ]
 */
```

#### Triggered counters

```php
$response->triggeredCounters->count();     // 2
$response->triggeredCounters->counters();
/**
    [
        'CONTRACARGOSREPORTADOS' => '1',
        'HISTORICONEGATIVO' => '1',
    ]
 */
$response->triggeredCounters->toArray();
/**
    [
        'CONTRACARGOSREPORTADOS' => '1',
        'HISTORICONEGATIVO' => '1',
    ]
 */
```

#### Warnings

```php
$response->warnings->count();     // 2
$response->warnings->warnings();
/**
    [
        'THIS IS THE FIRST WARNING',
        'THIS IS THE SECOND WARNING',
    ]
 */
$response->warnings->toArray();
/**
    [
        'THIS IS THE FIRST WARNING',
        'THIS IS THE SECOND WARNING',
    ]
 */
```

#### Errors

```php
// Example of a failed response

$response->errors->isErrorResponse();   // true
$response->errors->count();             // 2
$response->errors->code();              // '323'
$response->errors->key();               // 'The website identifier that was created in the Agent Web Console (’DEFAULT’ is the default website ID) does not match what was created in the AWC.'
$response->errors->errors();
/**
    [
        '323 BAD_SITE Cause: [[TEST] does not exist for merchant [201000]], Field: [SITE], Value: [TEST]',
        '362 BAD_CART Cause: [Shopping cart type index[0] is missing], Field: [PROD_TYPE], Value: [1=>111, 2=>234]',
    ]
 */

$response->errors->toArray();
/**
    [
        'isErrorResponse' => true,
        'code' => '323',
        'key' => 'The website identifier that was created in the Agent Web Console (’DEFAULT’ is the default website ID) does not match what was created in the AWC.',
        'errors' => [
            '323 BAD_SITE Cause: [[TEST] does not exist for merchant [201000]], Field: [SITE], Value: [TEST]',
            '362 BAD_CART Cause: [Shopping cart type index[0] is missing], Field: [PROD_TYPE], Value: [1=>111, 2=>234]',
        ],
    ]
 */
```

#### Additional information
```php
$response->additional->dateSinceFirstMadeTransaction();     //  '2017-05-30'
$response->additional->screenResolution();                  //  '768x1366'
$response->additional->userAgent();                         //  'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
$response->additional->operativeSystem();                   //  'Linux'
$response->additional->browser();                           //  'Chrome 58.0.3029.110'
$response->additional->wasPreviouslyWhitelisted();          //  false
$response->additional->toArray();
/**
    [
        'dateSinceFirstMadeTransaction' => '2017-05-30',
        'screenResolution' => '768x1366',
        'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
        'operativeSystem' => 'Linux',
        'browser' => 'Chrome 58.0.3029.110',
        'wasPreviouslyWhitelisted' => false,
    ]
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

After this mock instance is loaded the available options to mock are this ones. Those are passed via `payment.reference`, meaning the reference on the transaction  

* AUTH_ERR - Simulates a bad or expired ApiKey
* REVIEW - Simulates a review response
* DECLINE - Simulates a declination response
* EXCEPTION - Simulates an internal exception

Any other reference would return an approved response

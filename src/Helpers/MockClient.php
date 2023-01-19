<?php

namespace PlacetoPay\Kount\Helpers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class MockClient
{
    private static $instance;

    protected RequestInterface $request;
    protected array $lastResponse = [];
    protected mixed $data;

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
            'Content-Type' => 'text/plain;charset=UTF-8',
            'Content-Length' => '68',
            'Connection' => 'keep-alive',
            'Strict-Transport-Security' => 'max-age=15552000; includeSubDomains; preload',
            'Cache-control' => 'no-store, no-cache',
            'X-Frame-Options' => 'DENY',
            'CF-Cache-Status' => 'DYNAMIC',
            'Expect-CT' => 'max-age=604800, report-uri="https://report-uri.cloudflare.com/cdn-cgi/beacon/expect-ct"',
            'X-Content-Type-Options' => 'nosniff',
            'Server' => 'cloudflare',
            'CF-RAY' => '6e4d322b2971e5bb-BOG',
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
        parse_str($request->getBody()->getContents(), $data);
        $this->data = $data;

        return match ($data['MODE'] ?? null) {
            'Q' => $this->handleQuery(),
            'U' => $this->handleUpdate(),
            default => $this->response(400, 'Bad request'),
        };
    }

    /**
     * @throws Exception
     */
    public function handleQuery(): FulfilledPromise
    {
        $response = [
            'VERS' => '0720',
            'MODE' => 'Q',
            'TRAN' => 'KNZ50' . substr(time(), 0, 7),
            'MERC' => $this->getData('MERC'),
            'SESS' => $this->getData('SESS'),
            'ORDR' => $this->getData('ORDR'),
            'AUTO' => 'A',
            'SCOR' => '30',
            'OMNISCORE' => '67',
            'GEOX' => 'CO',
            'BRND' => 'VISA',
            'REGN' => 'CO_02',
            'NETW' => 'N',
            'KAPT' => 'N',
            'CARDS' => '1',
            'DEVICES' => '1',
            'EMAILS' => '1',
            'VELO' => '0',
            'VMAX' => '0',
            'SITE' => 'DEFAULT',
            'DEVICE_LAYERS' => 'DF651ACF30..99CF09F417.E3D16F2CB7.061826EF2B',
            'FINGERPRINT' => '4C2410BA22A64E21BF0C73EA88E48D7E',
            'TIMEZONE' => '300',
            'LOCALTIME' => '2017-05-31 00:19',
            'REGION' => 'CO_02',
            'COUNTRY' => 'CO',
            'PROXY' => 'N',
            'JAVASCRIPT' => 'Y',
            'FLASH' => 'N',
            'COOKIES' => 'Y',
            'HTTP_COUNTRY' => 'US',
            'LANGUAGE' => 'EN',
            'MOBILE_DEVICE' => 'N',
            'MOBILE_TYPE' => '',
            'MOBILE_FORWARDER' => 'N',
            'VOICE_DEVICE' => 'N',
            'PC_REMOTE' => 'N',
            'RULES_TRIGGERED' => '1',
            'RULE_ID_0' => '693746',
            'RULE_DESCRIPTION_0' => 'GEOX Lower Risk Review Countries',
            'COUNTERS_TRIGGERED' => '1',
            'COUNTER_NAME_0' => 'PAISESPARAREVISION',
            'COUNTER_VALUE_0' => '1',
            'REASON_CODE' => '',
            'DDFS' => '',
            'DSR' => '1050x1680',
            'UAS' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML like Gecko) Chrome/76.0.3809.100 Safari/537.36',
            'BROWSER' => '',
            'OS' => 'Mac OS X 10.14.6',
            'PIP_IPAD' => '',
            'PIP_LAT' => '',
            'PIP_LON' => '',
            'PIP_COUNTRY' => '',
            'PIP_REGION' => '',
            'PIP_CITY' => '',
            'PIP_ORG' => '',
            'IP_IPAD' => '181.128.85.221',
            'IP_LAT' => '6.2518',
            'IP_LON' => '-75.5636',
            'IP_COUNTRY' => 'CO',
            'IP_REGION' => 'Antioquia',
            'IP_CITY' => 'Medellin',
            'IP_ORG' => 'UNE',
            'WARNING_COUNT' => '1',
            'WARNING_0' => '401 EXTRA_DATA in request. Please see RIS spec for valid fields and ensure RIS input is URL encoded.',
            'PREVIOUSLY_WHITELISTED' => 'N',
            'THREE_DS_MERCHANT_RESPONSE' => '',
        ];

        switch ($this->getData('ORDR')) {
            case 'AUTH_ERR':
                $response = [
                    'MODE' => 'E',
                    'ERRO' => '501',
                    'ERROR_0' => '501 UNAUTH_REQ',
                    'ERROR_COUNT' => '1',
                    'WARNING_COUNT' => '0',
                ];
                break;
            case 'REVIEW':
                $response['AUTO'] = 'R';
                break;
            case 'DECLINE':
                $response['AUTO'] = 'D';
                break;
            case 'EXCEPTION':
                throw new Exception('Testing purposes exception');
            default:
                $response['AUTO'] = 'A';
                break;
        }

        return $this->response(200, $this->parseResponse($response));
    }

    public function getData(string $attribute): ?string
    {
        return $this->data[$attribute] ?? null;
    }

    private function parseResponse(array $response): string
    {
        $this->lastResponse = $response;

        foreach ($response as $key => $value) {
            $response[$key] = $key . '=' . $value;
        }

        return implode("\n", $response);
    }

    public static function client(): Client
    {
        return new Client(['handler' => HandlerStack::create(
            self::instance()
        )]);
    }

    private function handleUpdate(): FulfilledPromise
    {
        $response = [
            'VERS' => '0720',
            'MODE' => 'U',
            'TRAN' => $this->getData('TRAN'),
            'MERC' => $this->getData('MERC'),
            'SESS' => $this->getData('SESS'),
            'RULES_TRIGGERED' => '0',
            'COUNTERS_TRIGGERED' => '0',
            'REASON_CODE' => '',
            'DDFS' => '',
            'DSR' => '',
            'UAS' => '',
            'BROWSER' => '',
            'OS' => '',
            'PIP_IPAD' => '',
            'PIP_LAT' => '',
            'PIP_LON' => '',
            'PIP_COUNTRY' => '',
            'PIP_REGION' => '',
            'PIP_CITY' => '',
            'PIP_ORG' => '',
            'IP_IPAD' => '',
            'IP_LAT' => '',
            'IP_LON' => '',
            'IP_COUNTRY' => '',
            'IP_REGION' => '',
            'IP_CITY' => '',
            'IP_ORG' => '',
            'WARNING_0' => '401 EXTRA_DATA in request. Please see RIS spec for valid fields, and ensure RIS input is URL encoded.',
            'WARNING_COUNT' => '1',
        ];

        if ($this->getData('SESS') == 'AUTH_ERR') {
            $response = [
                'MODE' => 'E',
                'ERRO' => '501',
                'ERROR_0' => '501 UNAUTH_REQ',
                'ERROR_COUNT' => '1',
                'WARNING_COUNT' => '0',
            ];
        }

        return $this->response(200, $this->parseResponse($response));
    }
}

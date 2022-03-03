<?php

namespace PlacetoPay\Kount\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class MockClient
{
    private static $instance;

    /**
     * @var RequestInterface
     */
    protected $request;
    protected $data;

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

    public function request()
    {
        return $this->request;
    }

    public function data()
    {
        return $this->data;
    }

    public function response($code, $body, $headers = [], $reason = null)
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

    public function __invoke(RequestInterface $request, array $options)
    {
        $this->request = $request;
        parse_str($request->getBody()->getContents(), $data);
        $this->data = $data;

        switch ($data['MODE'] ?? null) {
            case 'Q':
                return $this->handleQuery();
            default:
                return $this->response(400, 'Bad request');
        }
    }

    public function handleQuery()
    {
        $response = '';

        switch ($this->getData('SESS')) {
            case 'AUTH_ERR':
                $response = json_decode('"MODE=E\nERRO=501\nERROR_0=501 UNAUTH_REQ\nERROR_COUNT=1\nWARNING_COUNT=0"');
                break;
        }

        return $this->response(200, $response);
    }

    public function getData(string $attribute)
    {
        return $this->data[$attribute] ?? null;
    }

    public static function client(): Client
    {
        return new Client(['handler' => HandlerStack::create(
            self::instance()
        )]);
    }
}

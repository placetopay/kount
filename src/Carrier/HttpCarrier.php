<?php


namespace PlacetoPay\Kount\Carrier;


use PlacetoPay\Kount\Contracts\Carrier;

class HttpCarrier implements Carrier
{

    public function riskRequest($url, $method, $data = [], $headers = [])
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'headers' => $headers,
            'form_params' => $data,
        ]);
        return $response->getBody()->getContents();
    }

}
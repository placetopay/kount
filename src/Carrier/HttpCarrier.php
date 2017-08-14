<?php


namespace PlacetoPay\Kount\Carrier;


use PlacetoPay\Kount\Contracts\Carrier;

class HttpCarrier implements Carrier
{

    public function riskRequest($url, $method, $data = [], $headers = [])
    {
        $client = new \GuzzleHttp\Client();
        $data = [
            'headers' => $headers,
            'form_params' => $data,
        ];

        if ($method == 'POST') {
            $response = $client->post($url, $data);
        } else if ($method == 'GET') {
            $response = $client->get($url, $data);
        } else if ($method == 'PUT') {
            $response = $client->put($url, $data);
        } else {
            throw new \Exception("No valid method for this request");
        }

        return $response->getBody()->getContents();
    }

}
<?php


namespace PlacetoPay\Kount\Contracts;


interface Carrier
{

    public function riskRequest($url, $method, $data = [], $headers = []);

}
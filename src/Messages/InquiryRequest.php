<?php

namespace PlacetoPay\Kount\Messages;

class InquiryRequest extends Request
{
    private $requestData = [];

    public function __construct($session, $data = [])
    {
        parent::__construct($session, $data);

        $this->mode = self::MODE_INQUIRY;
    }

    public function asRequestData(): array
    {
        $this->setSystemInformation();

        $this->setPaymentInformation();

        $this->setBillingInformation();

        $this->setShippingInformation();

        $this->setProductDetailsInformation();

        $this->setMiscellaneousInformation();

        return $this->requestData;
    }

    private function setSystemInformation(): void
    {
        $this->requestData = [
            'VERS' => $this->version,
            'MERC' => $this->merchant,
            'MODE' => $this->mode,
            'SESS' => $this->session,
            'SITE' => $this->website,
            'IPAD' => $this->data['ipAddress'],
            'ORDR' => $this->data['payment']['reference'],
        ];

        if (isset($this->data['mack'])) {
            $this->requestData['MACK'] = $this->data['mack'];
        }
    }

    private function setPaymentInformation(): void
    {
        $this->requestData['TOTL'] = $this->parseAmount($this->data['payment']['amount']['total']);
        $this->requestData['CURR'] = $this->data['payment']['amount']['currency'];

        if (!isset($this->data['cardNumber'])) {
            return;
        }

        $cardExpiration = explode('/', $this->data['cardExpiration']);

        $this->requestData = array_merge($this->requestData, [
            'PTOK' => $this->maskCardNumber($this->data['cardNumber']),
            'LAST4' => substr($this->data['cardNumber'], -4),
            'PTYP' => 'CARD',
            'PENC' => 'MASK',
            'CCMM' => $cardExpiration[0],
            'CCYY' => '20' . $cardExpiration[1],
        ]);

        if (isset($this->data['cvvStatus'])) {
            $this->requestData['CVVR'] = $this->data['cvvStatus'];
        }
    }

    private function setBillingInformation(): void
    {
        if (isset($this->data['payer'])) {
            $payer = $this->data['payer'];

            if (isset($payer['email'])) {
                $this->requestData['EMAL'] = $payer['email'];
            }

            if (isset($payer['name'])) {
                $this->requestData['NAME'] = $payer['name'] . (isset($payer['surname']) ? ' ' . $payer['surname'] : '');
            }

            if (isset($payer['mobile'])) {
                $this->requestData['B2PN'] = $payer['mobile'];
            }
        }

        if (isset($this->data['payer']['address'])) {
            $address = $this->data['payer']['address'];

            $this->requestData = array_merge(
                $this->requestData,
                [
                    'B2A1' => $address['street'] ?? null,
                    'B2CI' => $address['city'] ?? null,
                    'B2ST' => $address['state'] ?? null,
                    'B2PC' => $address['postalCode'] ?? null,
                    'B2CC' => $address['country'] ?? null,
                    'B2PN' => $address['phone'] ?? null,
                ]
            );
        }
    }

    private function setShippingInformation(): void
    {
        if (!isset($this->data['payment']['shipping'])) {
            return;
        }

        $shipping = $this->data['payment']['shipping'];
        $address = $shipping['address'] ?? [];

        $this->requestData = array_merge(
            $this->requestData,
            [
                'S2A1' => $address['street'] ?? null,
                'S2CI' => $address['city'] ?? null,
                'S2CC' => $address['country'] ?? null,
                'S2EM' => $shipping['email'] ?? null,
                'S2NM' => $shipping['name'] . ' ' . $shipping['surname'],
                'S2PC' => $address['postalCode'] ?? null,
                'S2PN' => $address['phone'] ?? null,
                'S2ST' => $address['state'] ?? null,
            ]
        );
    }

    private function setProductDetailsInformation(): void
    {
        if (!isset($this->data['payment']['items'])) {
            return;
        }

        foreach ($this->data['payment']['items'] as $index => $item) {
            $this->requestData = array_merge(
                $this->requestData,
                [
                    'PROD_DESC[' . $index . ']' => $item['desc'] ?? null,
                    'PROD_ITEM[' . $index . ']' => $item['name'] ?? null,
                    'PROD_PRICE[' . $index . ']' => isset($item['price']) ? $this->parseAmount($item['price']) : null,
                    'PROD_QUANT[' . $index . ']' => $item['qty'] ?? null,
                    'PROD_TYPE[' . $index . ']' => $item['sku'] ?? null,
                ]
            );
        }
    }

    private function setMiscellaneousInformation(): void
    {
        if (isset($this->data['cash'])) {
            $this->requestData['CASH'] = $this->data['cash'];
        }

        if (isset($this->data['epoc'])) {
            $this->requestData['EPOC'] = $this->data['epoc'];
        }

        if (isset($this->data['dob'])) {
            $this->requestData['DOB'] = $this->data['dob'];
        }

        if (isset($this->data['gender'])) {
            $this->requestData['GENDER'] = $this->data['gender'];
        }

        if (isset($this->data['payer']['documentType']) && isset($this->data['payer']['document'])) {
            $this->requestData['UNIQ'] = $this->data['payer']['documentType'] . $this->data['payer']['document'];
        }

        $this->requestData['UAGT'] = $this->data['userAgent'];

        if (isset($this->data['additional'])) {
            foreach ($this->data['additional'] as $key => $value) {
                $this->requestData['UDF[' . strtoupper($key) . ']'] = $value;
            }
        }
    }

    private function maskCardNumber(string $number): ?string
    {
        if (preg_match('/^\d{13,19}$/', $number)) {
            $repeats = strlen($number) - 10;
            return substr($number, 0, 6) . str_repeat('X', $repeats > 4 ? $repeats : 5) . substr($number, -4);
        }

        return null;
    }
}

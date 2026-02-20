<?php

namespace PlacetoPay\Kount\Messages\Requests;

use PlacetoPay\Kount\Constants\PaymentTypes;
use PlacetoPay\Kount\Helpers\AmountHelper;
use PlacetoPay\Kount\Helpers\ArrayHelper;

class CreateOrder extends Base
{
    public function body(): array
    {
        $this->requestData = [
            'merchantOrderId' => (string)ArrayHelper::get($this->data, 'payment.reference'),
            'channel' => $this->channel,
            'deviceSessionId' => (string)ArrayHelper::get($this->data, 'instrument.kount.session'),
            'creationDateTime' => ArrayHelper::get($this->data, 'date'),
            'items' => [],
        ];

        if (isset($this->data['ipAddress'])) {
            $this->requestData['userIp'] = $this->data['ipAddress'];
        }

        $this->setAccountInformation();
        $this->setItemsInformation();
        $this->setTransactionInformation();
        $this->setPromotions();
        $this->setLoyalty();
        $this->setShippingInformation();
        $this->setCustomAttributes();
        $this->setMerchant();
        $this->setAdvertising();
        $this->setLinks();

        return ArrayHelper::filterValues($this->requestData);
    }

    private function setAccountInformation(): void
    {
        if (($documentType = ArrayHelper::get($this->data, 'payer.documentType')) && ($document = ArrayHelper::get($this->data, 'payer.document'))) {
            $this->requestData['account'] = [
                'id' => $documentType . $document,
            ];
        }

        if (isset($this->data['account'])) {
            $this->requestData['account'] = [
                'id' => (string)ArrayHelper::get($this->data, 'account.id'),
                'type' => (string)ArrayHelper::get($this->data, 'account.type'),
                'creationDateTime' => ArrayHelper::get($this->data, 'account.creationDateTime'),
                'username' => (string)ArrayHelper::get($this->data, 'account.username'),
                'accountIsActive' => isset($this->data['account']['accountIsActive']) ? filter_var($this->data['account']['accountIsActive'], FILTER_VALIDATE_BOOLEAN) : null,
            ];
        }
    }

    private function setItemsInformation(): void
    {
        foreach (ArrayHelper::get($this->data, 'payment.items', []) as $item) {
            $this->requestData['items'][] = [
                'id' => (string)ArrayHelper::get($item, 'id'),
                'price' => isset($item['price']) && $this->getCurrency() ? AmountHelper::parseAmount($item['price'], $this->getCurrency(), $this->amountInMinorUnit()) : null,
                'description' => (string)ArrayHelper::get($item, 'description'),
                'quantity' => ArrayHelper::get($item, 'qty'),
                'sku' => (string)ArrayHelper::get($item, 'sku'),
                'category' => ArrayHelper::get($item, 'category'),
                'isDigital' => isset($item['additional']['category']) ? $item['additional']['category'] == 'digital' : (isset($item['additional']['isDigital']) ? filter_var(ArrayHelper::get($item, 'additional.isDigital'), FILTER_VALIDATE_BOOLEAN) : null),
                'subCategory' => (string)ArrayHelper::get($item, 'additional.subCategory'),
                'upc' => (string)ArrayHelper::get($item, 'additional.upc'),
                'brand' => (string)ArrayHelper::get($item, 'additional.brand'),
                'url' => (string)ArrayHelper::get($item, 'additional.url'),
                'imageUrl' => (string)ArrayHelper::get($item, 'additional.imageUrl'),
                'physicalAttributes' => [
                    'color' => (string)ArrayHelper::get($item, 'additional.attributes.color'),
                    'size' => (string)ArrayHelper::get($item, 'additional.attributes.size'),
                    'weight' => (string)ArrayHelper::get($item, 'additional.attributes.weight'),
                    'height' => (string)ArrayHelper::get($item, 'additional.attributes.height'),
                    'width' => (string)ArrayHelper::get($item, 'additional.attributes.width'),
                    'depth' => (string)ArrayHelper::get($item, 'additional.attributes.depth'),
                ],
                'descriptors' => array_reduce(ArrayHelper::get($item, 'additional.descriptors', []), function ($carry, $item) {
                    return $carry && is_string($item);
                }, true) ? ArrayHelper::get($item, 'additional.descriptors') : null,
                'isService' => isset($item['additional']['isService']) ? filter_var(ArrayHelper::get($item, 'additional.isService'), FILTER_VALIDATE_BOOLEAN) : null,
            ];
        }
    }

    private function setTransactionInformation(): void
    {
        $subtotal = array_values(array_filter(ArrayHelper::get($this->data, 'payment.amount.details', []), function ($value) {
            return isset($value['kind']) && $value['kind'] === 'subtotal';
        }))[0] ?? [];

        $transaction = [
            'processor' => (string)ArrayHelper::get($this->data, 'transaction.processor'),
            'processorMerchantId' => (string)ArrayHelper::get($this->data, 'transaction.processorId'),
            'subtotal' => isset($subtotal['amount']) && $this->getCurrency() ? AmountHelper::parseAmount($subtotal['amount'], $this->getCurrency(), $this->amountInMinorUnit()) : null,
            'orderTotal' => isset($this->data['payment']['amount']['total']) && $this->getCurrency() ? AmountHelper::parseAmount($this->data['payment']['amount']['total'], $this->getCurrency(), $this->amountInMinorUnit()) : null,
            'currency' => $this->getCurrency(),
            'merchantTransactionId' => (string)ArrayHelper::get($this->data, 'payment.reference'),
        ];

        if (isset($this->data['payment']['amount']['taxes'])) {
            $transaction['tax'] = [
                'isTaxable' => true,
                'taxableCountryCode' => (string)ArrayHelper::get($this->data, 'payment.amount.taxCountry'),
            ];

            $total = 0;
            $outOfStateTotal = 0;

            foreach (ArrayHelper::get($this->data, 'payment.amount.taxes') as $tax) {
                $total += $tax['amount'] ?? 0;
                if (isset($tax['kind']) && $tax['kind'] === 'stateTax') {
                    $outOfStateTotal += $tax['amount'];
                }
            }

            if ($total) {
                $transaction['tax']['taxAmount'] = AmountHelper::parseAmount($total, $this->getCurrency(), $this->amountInMinorUnit());
            }

            if ($outOfStateTotal) {
                $transaction['tax']['outOfStateTotal'] = AmountHelper::parseAmount($outOfStateTotal, $this->getCurrency(), $this->amountInMinorUnit());
            }
        }

        if (isset($this->data['payer'])) {
            $transaction['billedPerson'] = $this->getPerson('payer');
        }

        if ($this->requestData['items']) {
            $transaction['items'] = $this->getResumeOfItems();
        }

        if (ArrayHelper::get($this->data, 'instrument.type')) {
            $transaction['payment']['type'] = (string)ArrayHelper::get($this->data, 'instrument.type');
        }

        if (isset($this->data['instrument']['card'])) {
            $transaction['payment']['type'] = ArrayHelper::get($transaction, 'payment.type') ?? PaymentTypes::CARD;
            $transaction['payment']['bin'] = (string)ArrayHelper::get($this->data, 'instrument.card.bin');
            $transaction['payment']['last4'] = (string)ArrayHelper::get($this->data, 'instrument.card.last4');
            $transaction['payment']['cardBrand'] = (string)ArrayHelper::get($this->data, 'instrument.card.cardBrand');
        } elseif (isset($this->data['instrument']['token'])) {
            $transaction['payment']['type'] = ArrayHelper::get($transaction, 'payment.type') ?? PaymentTypes::TOKEN;
            $transaction['payment']['paymentToken'] = (string)ArrayHelper::get($this->data, 'instrument.token.token');
        } else {
            $transaction['payment']['type'] = ArrayHelper::get($transaction, 'payment.type') ?? PaymentTypes::NONE;
        }

        $transaction['transactionStatus'] = (string)ArrayHelper::get($this->data, 'transaction.status');

        $transaction['authorizationStatus'] = [
            'authResult' => (string)ArrayHelper::get($this->data, 'transaction.authResult'),
            'dateTime' => (string)ArrayHelper::get($this->data, 'transaction.date'),
            'declineCode' => (string)ArrayHelper::get($this->data, 'transaction.declineCode'),
            'processorAuthCode' => (string)ArrayHelper::get($this->data, 'transaction.authorization'),
            'processorTransactionId' => (string)ArrayHelper::get($this->data, 'transaction.processorId'),
            'acquirerReferenceNumber' => (string)ArrayHelper::get($this->data, 'transaction.receipt'),
            'verificationResponse' => [
                'cvvStatus' => (string)ArrayHelper::get($this->data, 'transaction.verification.cvvStatus'),
                'avsStatus' => (string)ArrayHelper::get($this->data, 'transaction.verification.avsStatus'),
            ],
        ];

        $this->requestData['transactions'][] = $transaction;
    }

    protected function getPerson(string $type): array
    {
        $array = ArrayHelper::get($this->data, $type, []);

        return [
            'name' => [
                'first' => (string)ArrayHelper::get($array, 'name'),
                'family' => (string)ArrayHelper::get($array, 'surname'),
                'preferred' => (string)ArrayHelper::get($array, 'preferred'),
                'middle' => (string)ArrayHelper::get($array, 'middle'),
                'prefix' => (string)ArrayHelper::get($array, 'prefix'),
                'suffix' => (string)ArrayHelper::get($array, 'suffix'),
            ],
            'emailAddress' => (string)ArrayHelper::get($array, 'email'),
            'phoneNumber' => (string)ArrayHelper::get($array, 'mobile'),
            'dateOfBirth' => (string)ArrayHelper::get($array, 'dateOfBirth'),
            'address' => [
                'line1' => (string)ArrayHelper::get($array, 'address.street'),
                'line2' => (string)ArrayHelper::get($array, 'address.street2'),
                'city' => (string)ArrayHelper::get($array, 'address.city'),
                'region' => (string)ArrayHelper::get($array, 'address.state'),
                'countryCode' => (string)ArrayHelper::get($array, 'address.country'),
                'postalCode' => (string)ArrayHelper::get($array, 'address.postalCode'),
            ],
        ];
    }

    private function setPromotions(): void
    {
        if (!isset($this->data['promotions'])) {
            return;
        }
        $promotions = [];

        foreach ($this->data['promotions'] as $promotion) {
            $newPromotion = [];
            $newPromotion['id'] = (string)ArrayHelper::get($promotion, 'id');
            $newPromotion['description'] = (string)ArrayHelper::get($promotion, 'description');
            $newPromotion['status'] = (string)ArrayHelper::get($promotion, 'status');
            $newPromotion['statusReason'] = (string)ArrayHelper::get($promotion, 'statusReason');

            if (isset($promotion['discount'])) {
                $discountCurrency = $promotion['discount']['currency'] ?? $this->getCurrency() ?? null;

                $newPromotion['discount'] = [
                    'percentage' => ArrayHelper::get($promotion, 'discount.percentage'),
                    'amount' => isset($promotion['discount']['amount']) && $discountCurrency ? AmountHelper::parseAmount($promotion['discount']['amount'], $discountCurrency, $this->amountInMinorUnit()) : null,
                    'currency' => $discountCurrency,
                ];
            }

            if (isset($promotion['credit'])) {
                $creditCurrency = $promotion['credit']['currency'] ?? $this->getCurrency() ?? null;

                $newPromotion['credit'] = [
                    'creditType' => ArrayHelper::get($promotion, 'credit.creditType'),
                    'amount' => isset($promotion['credit']['amount']) && $creditCurrency ? AmountHelper::parseAmount($promotion['credit']['amount'], $creditCurrency, $this->amountInMinorUnit()) : null,
                    'currency' => $creditCurrency,
                ];
            }

            $promotions[] = $newPromotion;
        }

        $this->requestData['promotions'] = $promotions;
    }

    private function setLoyalty(): void
    {
        if (!isset($this->data['loyalty'])) {
            return;
        }

        $currency = $this->data['loyalty']['credit']['currency'] ?? $this->getCurrency() ?? '';

        $this->requestData['loyalty'] = [
            'id' => (string)ArrayHelper::get($this->data, 'loyalty.id'),
            'description' => (string)ArrayHelper::get($this->data, 'loyalty.description'),
            'credit' => [
                'creditType' => (string)ArrayHelper::get($this->data, 'loyalty.credit.creditType'),
                'amount' => AmountHelper::parseAmount($this->data['loyalty']['credit']['amount'], $currency, $this->amountInMinorUnit()),
                'currency' => $currency,
            ],
        ];
    }

    protected function amountInMinorUnit(): bool
    {
        return ArrayHelper::get($this->data, 'payment.amount.inMinorUnit', false);
    }

    private function setShippingInformation(): void
    {
        $fulfillment = [
            'type' => (string)ArrayHelper::get($this->data, 'shipping.type', ''),
            'status' => (string)ArrayHelper::get($this->data, 'shipping.status'),
            'accessUrl' => (string)ArrayHelper::get($this->data, 'shipping.accessUrl'),
            'downloadDeviceIp' => (string)ArrayHelper::get($this->data, 'shipping.downloadDeviceIp'),
            'merchantFulfillmentId' => (string)ArrayHelper::get($this->data, 'payment.reference'),
            'recipientPerson' => $this->getPerson('payment.shipping'),
            'shipping' => [
                'provider' => (string)ArrayHelper::get($this->data, 'shipping.provider'),
                'trackingNumber' => (string)ArrayHelper::get($this->data, 'shipping.trackingNumber'),
                'method' => (string)ArrayHelper::get($this->data, 'shipping.method'),
            ],
        ];

        $shipping = array_values(
            array_filter(
                ArrayHelper::get($this->data, 'payment.amount.details', []),
                function ($value) {
                    return isset($value['kind']) && $value['kind'] === 'shipping';
                }
            )
        )[0] ?? [];

        $fulfillment['shipping']['amount'] =
            isset($shipping['amount']) && $this->getCurrency() ?
                AmountHelper::parseAmount($shipping['amount'], $this->getCurrency(), $this->amountInMinorUnit())
                : null;

        if ($this->requestData['items']) {
            $fulfillment['items'] = $this->getResumeOfItems();
        }

        if (isset($this->data['shipping']['digitalDownloaded'])) {
            $fulfillment['digitalDownloaded'] = filter_var($this->data['shipping']['digitalDownloaded'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($this->data['store'])) {
            $fulfillment['store'] = [
                'id' => (string)ArrayHelper::get($this->data, 'store.id'),
                'name' => (string)ArrayHelper::get($this->data, 'store.name'),
            ];

            if (isset($this->data['store']['address'])) {
                $fulfillment['store']['address'] = [
                    'line1' => (string)ArrayHelper::get($this->data, 'store.address.street'),
                    'line2' => (string)ArrayHelper::get($this->data, 'store.address.street2'),
                    'city' => (string)ArrayHelper::get($this->data, 'store.address.city'),
                    'region' => (string)ArrayHelper::get($this->data, 'store.address.state'),
                    'countryCode' => (string)ArrayHelper::get($this->data, 'store.address.country'),
                    'postalCode' => (string)ArrayHelper::get($this->data, 'store.address.postalCode'),
                ];
            }
        }

        $this->requestData['fulfillment'][] = $fulfillment;
    }

    protected function getCurrency(): ?string
    {
        return ArrayHelper::get($this->data, 'payment.amount.currency');
    }

    protected function getResumeOfItems(): array
    {
        return array_map(function ($item) {
            return array_map(fn ($item) => (string)$item, array_intersect_key($item, array_flip(['id', 'quantity'])));
        }, $this->requestData['items']);
    }

    private function setCustomAttributes(): void
    {
        if (!isset($this->data['additional'])) {
            return;
        }

        foreach ($this->data['additional'] as $key => $value) {
            $this->requestData['customFields'][$key] = $value;
        }
    }

    private function setLinks(): void
    {
        if (!isset($this->data['links'])) {
            return;
        }

        $this->requestData['links'] = [
            'viewOrderUrl' => (string)ArrayHelper::get($this->data, 'links.viewOrderUrl'),
            'requestRefundUrl' => (string)ArrayHelper::get($this->data, 'links.requestRefundUrl'),
            'buyAgainUrl' => (string)ArrayHelper::get($this->data, 'links.buyAgainUrl'),
            'writeReviewUrl' => (string)ArrayHelper::get($this->data, 'links.writeReviewUrl'),
        ];
    }

    private function setAdvertising(): void
    {
        if (!isset($this->data['advertising'])) {
            return;
        }

        $this->requestData['advertising'] = [
            'channel' => (string)ArrayHelper::get($this->data, 'advertising.channel'),
            'affiliate' => (string)ArrayHelper::get($this->data, 'advertising.affiliate'),
            'subAffiliate' => (string)ArrayHelper::get($this->data, 'advertising.subAffiliate'),
            'writeReviewUrl' => (string)ArrayHelper::get($this->data, 'advertising.writeReviewUrl'),
            'events' => ArrayHelper::get($this->data, 'advertising.events', []),
            'campaign' => isset($this->data['advertising']['campaign']) ? [
                'id' => $this->data['advertising']['campaign']['id'] ?? null,
                'name' => $this->data['advertising']['campaign']['name'] ?? null,
            ] : null,
        ];
    }

    private function setMerchant(): void
    {
        if (!isset($this->data['merchant'])) {
            return;
        }

        $this->requestData['merchantCategoryCode'] = (string)ArrayHelper::get($this->data, 'merchant.merchantCategoryCode');

        $this->requestData['merchant'] = [
            'name' => (string)ArrayHelper::get($this->data, 'merchant.name'),
            'storeName' => (string)ArrayHelper::get($this->data, 'merchant.storeName'),
            'websiteUrl' => (string)ArrayHelper::get($this->data, 'merchant.websiteUrl'),
            'id' => (string)ArrayHelper::get($this->data, 'merchant.id'),
            'contactEmail' => (string)ArrayHelper::get($this->data, 'merchant.contactEmail'),
            'contactPhoneNumber' => (string)ArrayHelper::get($this->data, 'merchant.contactPhoneNumber'),
        ];
    }
}

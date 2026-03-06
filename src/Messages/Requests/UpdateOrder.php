<?php

namespace PlacetoPay\Kount\Messages\Requests;

use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Helpers\ArrayHelper;

class UpdateOrder extends Base
{
    public function method(): string
    {
        return 'PATCH';
    }

    public function body(): array
    {
        $this->requestData['merchantOrderId'] = (string)ArrayHelper::get($this->data, 'payment.reference');
        $this->requestData['deviceSessionId'] = (string)ArrayHelper::get($this->data, 'kountSessionId');

        if (isset($this->data['riskInquiry'])) {
            $this->requestData['riskInquiry'] = [
                'decision' => (string)ArrayHelper::get($this->data, 'riskInquiry.decision'),
                'reasonCode' => (string)ArrayHelper::get($this->data, 'riskInquiry.reasonCode'),
            ];
        }

        return ArrayHelper::filterValues($this->requestData);
    }

    /**
     * @throws KountServiceException
     */
    public function url(): string
    {
        if (!isset($this->data['orderId'])) {
            throw new KountServiceException('The orderId is required to update an order.');
        }

        return parent::url() . '/' . $this->data['orderId'];
    }
}

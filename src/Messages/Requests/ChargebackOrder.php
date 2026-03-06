<?php

namespace PlacetoPay\Kount\Messages\Requests;

use PlacetoPay\Kount\Helpers\AmountHelper;
use PlacetoPay\Kount\Helpers\ArrayHelper;

class ChargebackOrder extends Base
{
    public function method(): string
    {
        return 'PATCH';
    }

    public function body(): array
    {
        $reversalUpdate = [
            'orderId' => (string)ArrayHelper::get($this->data, 'orderId'),
            'fraudReportType' => (string)ArrayHelper::get($this->data, 'fraudReportType'),
        ];

        if (isset($this->data['chargeback'])) {
            $reversalUpdate['chargeback'] = [
                'isChargeback' => true,
                'transactionId' => (string)ArrayHelper::get($this->data, 'chargeback.transactionId'),
                'reasonCode' => (string)ArrayHelper::get($this->data, 'chargeback.reasonCode'),
                'cardType' => (string)ArrayHelper::get($this->data, 'chargeback.cardType'),
            ];
        }

        if (isset($this->data['refund'])) {
            $reversalUpdate['refund'] = [
                'isRefund' => true,
                'transactionId' => (string)ArrayHelper::get($this->data, 'refund.transactionId'),
                'dateTime' => (string)ArrayHelper::get($this->data, 'refund.date'),
                'amount' => isset($this->data['refund']['amount']['total'], $this->data['refund']['amount']['currency']) ?
                    AmountHelper::parseAmount(
                        $this->data['refund']['amount']['total'],
                        $this->data['refund']['amount']['currency'],
                        $this->data['refund']['amount']['inMinorUnit'] ?? true
                    ) : null,
                'currency' => (string)ArrayHelper::get($this->data, 'refund.amount.currency'),
                'gatewayReceipt' => (string)ArrayHelper::get($this->data, 'refund.receipt'),
            ];
        }

        $this->requestData['reversalsUpdates'][] = $reversalUpdate;

        return ArrayHelper::filterValues($this->requestData);
    }

    public function url(): string
    {
        return parent::url() . ':batchUpdateReversals';
    }
}

<?php

namespace PlacetoPay\Kount\Messages\InquiryResponseInformationExperts;

use PlacetoPay\Kount\Contracts\InquiryResponseInformationExpert;
use PlacetoPay\Kount\Entities\KountError;
use PlacetoPay\Kount\Exceptions\KountServiceException;
use PlacetoPay\Kount\Messages\InquiryResponse;

class ErrorsInformationExpert extends InquiryResponseInformationExpert
{
    /**
     * @throws KountServiceException
     */
    public function __construct(InquiryResponse $parent)
    {
        parent::__construct($parent);

        if ($this->isErrorResponse()) {
            throw KountServiceException::forErrorResponse($this->errors());
        }
    }

    public function isErrorResponse(): bool
    {
        return $this->data('MODE') === 'E';
    }

    public function count(): int
    {
        return (int)$this->data('ERROR_COUNT');
    }

    public function code(): ?string
    {
        return $this->data('ERRO');
    }

    public function key(): ?string
    {
        if (is_null($this->code())) {
            return null;
        }

        return KountError::errorKey($this->code());
    }

    public function errors(): array
    {
        if (!$this->isErrorResponse()) {
            return [];
        }

        if ($this->count() === 0) {
            return [$this->key()];
        }

        $errors = [];

        for ($i = 0; $i < $this->data('ERROR_COUNT', 0); $i++) {
            $errors[] = $this->data("ERROR_$i");
        }

        return $errors;
    }

    public function toArray(): array
    {
        return [
            'isErrorResponse' => $this->isErrorResponse(),
            'code' => $this->code(),
            'key' => $this->key(),
            'errors' => $this->errors(),
        ];
    }
}

<?php

namespace PlacetoPay\Kount\Messages\Responses;

use PlacetoPay\Kount\Helpers\ArrayHelper;

class Token extends Base
{
    public function expiresIn(): ?int
    {
        return $this->get('expires_in');
    }

    public function accessToken(): ?string
    {
        return $this->get('access_token');
    }

    public function tokenType(): ?string
    {
        return $this->get('token_type');
    }

    public function scope(): ?string
    {
        return $this->get('scope');
    }

    public function errors(): mixed
    {
        return ArrayHelper::filterValues([
            [
                'code' => $this->get('error'),
                'message' => $this->get('error_description'),
            ],
        ]);
    }
}

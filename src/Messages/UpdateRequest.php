<?php

namespace PlacetoPay\Kount\Messages;

class UpdateRequest extends Request
{
    public function __construct($session, $data = [])
    {
        parent::__construct($session, $data);

        $this->mode = self::MODE_UPDATE;
    }

    /**
     * @return array
     */
    public function asRequestData(): array
    {
        $requestData = [
            'VERS' => $this->version,
            'MODE' => $this->mode,
            'SITE' => $this->website,

            'MERC' => $this->merchant,
            'SESS' => $this->session,
            'MACK' => 'Y',
        ];

        // TODO: Validate this thing
        $requestData['TRAN'] = $this->data['id'];
        $requestData['AUTH'] = $this->data['status'];

        return $requestData;
    }
}

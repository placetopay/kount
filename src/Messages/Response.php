<?php


namespace PlacetoPay\Kount\Messages;


use PlacetoPay\Kount\Entities\KountError;

class Response
{

    protected $raw;
    protected $data;

    protected $errors = [];

    public function __construct($response)
    {
        $this->raw = $response;
        $lines = preg_split('/[\r\n]+/', $response, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);
            $this->data[$key] = $value;
        }
    }

    public function raw()
    {
        return $this->raw;
    }

    public function data($key = null)
    {
        if ($key) {
            if (isset($this->data[$key])) {
                return $this->data[$key];
            }
            return null;
        }
        return $this->data;
    }

    // State related

    public function isSuccessful()
    {
        if ($this->data('MODE') != 'E') {
            return true;
        }
        return false;
    }

    // Error related

    public function errorCode()
    {
        return $this->data('ERRO');
    }

    /**
     * Returns the KEY error that can be translated into an message
     * @return string
     */
    public function errorKey()
    {
        if ($this->errorCode()) {
            return KountError::errorKey($this->errorCode());
        }
        return null;
    }

    protected function loadErrors()
    {
        if (!$this->errors) {
            $i = 0;
            while ($error = $this->data('ERROR_' . $i)) {
                $this->errors[] = $this->data('ERROR_' . $i);
                $i++;
            }
        }
    }

    public function errorCount()
    {
        $this->loadErrors();
        return sizeof($this->errors);
    }

    public function errors()
    {
        $this->loadErrors();
        return $this->errors;
    }

    public function merchant()
    {
        return $this->data('MERC');
    }

    public function session()
    {
        return $this->data('SESS');
    }

    public function order()
    {
        return $this->data('ORDR');
    }

}
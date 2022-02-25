<?php

class BaseTestCase extends PHPUnit_Framework_TestCase
{
    public function serialize($data)
    {
        return base64_encode(serialize($data));
    }

    public function unserialize($coded)
    {
        return unserialize(base64_decode($coded));
    }
}

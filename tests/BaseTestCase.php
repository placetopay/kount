<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
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

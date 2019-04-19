<?php

namespace Negotiation\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function call_private_method($class, $method, $object, $params)
    {
        $method = new \ReflectionMethod($class, $method);

        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }
}

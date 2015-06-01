<?php

namespace Negotiation\Tests;

use Negotiation\AbstractNegotiator;
use Negotiation\Match;

class AbstractNegotiatorTest extends TestCase
{

    protected function call_private_method($class, $method, $object, $params) {
        $method = new \ReflectionMethod($class, $method);

        $method->setAccessible(TRUE);

        return $method->invokeArgs($object, $params);
    }

    /**
     * @dataProvider dataProviderForTestCompare
     */
    protected function testCompare($match1, $match2, $expected)
    {
        $return = $this->call_private_method('\Negotiation\AbstractNegotiator', 'compare', null, array($match1, $match2));

        $this->assertEquals($expected, $return);
    }

    public static function dataProviderForTestCompare()
    {
        return array(
            array(new Match('text/html', 1.0, 110, 1),  new Match('text/html', 1.0, 111, 1),    1),
            array(new Match('text/html', 1.0, 10, 1),   new Match('text/html', 1.0,   1, 1),   -1),
            array(new Match('text/html', 0.1, 10, 1),   new Match('text/html', 0.1,  10, 2),   -1),
            array(new Match('text/html', 0.4, 110, 1),  new Match('image/png', 0.6, 111, 1),    1),
            array(new Match('text/html', 1.0, 110, 1),  new Match('image/png', 0.4,  11, 1),   -1),
            array(new Match('text/html', 0.5, 110, 4),  new Match('image/png', 0.5,  11, 5),   -1),
            array(new Match('text/html', 0.5, 11, 5),   new Match('image/png', 0.5,  11, 5),    0),
        );
    }

}

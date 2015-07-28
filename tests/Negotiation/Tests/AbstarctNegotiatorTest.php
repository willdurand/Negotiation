<?php

namespace Negotiation\Tests;

use Negotiation\AbstractNegotiator;
use Negotiation\Match;

class AbstarctNegotiatorTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestReduce
     */
    public function testReduce($carry, $match, $expected)
    {
        $return = $this->call_private_method('\Negotiation\AbstractNegotiator', 'reduce', null, array($carry, $match));

        $this->assertEquals($expected, $return);
    }

    public static function dataProviderForTestReduce()
    {
        return array(
            array(
                array(1 => new Match(1.0, 10, 1)),
                new Match(0.5, 111, 1),
                array(1 => new Match(0.5, 111, 1)),
            ),
            array(
                array(1 => new Match(1.0, 110, 1)),
                new Match(0.5, 11, 1),
                array(1 => new Match(1.0, 110, 1)),
            ),
            array(
                array(0 => new Match(1.0, 10, 1)),
                new Match(0.5, 111, 1),
                array(0 => new Match(1.0, 10, 1), 1 => new Match(0.5, 111, 1)),
            ),
        );
    }

}

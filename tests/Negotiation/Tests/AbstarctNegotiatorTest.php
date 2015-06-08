<?php

namespace Negotiation\Tests;

use Negotiation\AbstractNegotiator;
use Negotiation\Match;

class AbstractNegotiatorTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestCompare
     */
    public function testCompare($match1, $match2, $expected)
    {
        $return = $this->call_private_method('\Negotiation\AbstractNegotiator', 'compare', null, array($match1, $match2));

        $this->assertEquals($expected, $return);
    }

    public static function dataProviderForTestCompare()
    {
        return array(
            array(new Match(1.0, 110, 1), new Match(1.0, 111, 1),    0),
            array(new Match(0.1, 10,  1), new Match(0.1,  10, 2),   -1),
            array(new Match(0.5, 110, 5), new Match(0.5,  11, 4),    1),
            array(new Match(0.4, 110, 1), new Match(0.6, 111, 3),    1),
            array(new Match(0.6, 110, 1), new Match(0.4, 111, 3),   -1),
        );
    }

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
                array(0 => new Match(1.0, 10, 1)),
                new Match(0.5, 111, 1),
                array(0 => new Match(1.0, 10, 1), 1 => new Match(0.5, 111, 1)),
            ),
        );
    }

}
